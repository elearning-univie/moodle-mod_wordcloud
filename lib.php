<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Wordcloud lib
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The only font the classic renderer can display.
 *
 * The classic view is built from CSS-styled spans whose rules (.w1 - .w6 in
 * styles.css) set a size but no font-family, so the words are always shown in
 * the theme's default sans-serif face. It can therefore only honour this one
 * font; every other font has to be drawn by the canvas-based modern renderer.
 */
define('WORDCLOUD_CLASSIC_FONT', 'Arial, sans-serif');

/**
 * Returns the information on whether the module supports a feature
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function wordcloud_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_COLLABORATION;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        default:
            return null;
    }
}

/**
 * wordcloud_add_instance
 *
 * @param array $wordcloud
 * @return bool
 */
function wordcloud_add_instance($wordcloud) {
    global $DB;

    $wordcloud->timecreated = time();

    mod_wordcloud_package_settings($wordcloud);

    $wordcloud->id = $DB->insert_record('wordcloud', $wordcloud);
    $completiontimeexpected = !empty($wordcloud->completionexpected) ? $wordcloud->completionexpected : null;
    \core_completion\api::update_completion_date_event($wordcloud->coursemodule, 'wordcloud', $wordcloud->id, $completiontimeexpected);

    return $wordcloud->id;
}

/**
 * wordcloud_delete_instance
 *
 * @param int $id
 * @return bool
 */
function wordcloud_delete_instance($id) {
    global $DB;

    if (!$DB->record_exists('wordcloud', ['id' => $id])) {
        return false;
    }

    $cm = get_coursemodule_from_instance('wordcloud', $id);
    if ($cm) {
        \core_completion\api::update_completion_date_event($cm->id, 'wordcloud', $id, null);
    }

    $DB->delete_records('wordcloud_map', ['wordcloudid' => $id]);
    $DB->delete_records('wordcloud', ['id' => $id]);

    return true;
}

/**
 * wordcloud_update_instance
 *
 * @param array $wordcloud
 * @return bool
 */
function wordcloud_update_instance($wordcloud) {
    global $DB;

    $wordcloud->timemodified = time();
    $wordcloud->id = $wordcloud->instance;

    if (!property_exists($wordcloud, 'usemonocolor') || !$wordcloud->usemonocolor) {
        $wordcloud->usemonocolor = 0;
    }

    if (!property_exists($wordcloud, 'visibility') || !$wordcloud->visibility) {
        $wordcloud->visibility = 0;
    }

    if (!property_exists($wordcloud, 'renderstyle') || !$wordcloud->renderstyle) {
        $wordcloud->renderstyle = 0;
    }

    mod_wordcloud_package_settings($wordcloud);

    $DB->update_record('wordcloud', $wordcloud);

    $completionexpected = !empty($wordcloud->completionexpected) ? $wordcloud->completionexpected : null;
    \core_completion\api::update_completion_date_event($wordcloud->coursemodule, 'wordcloud', $wordcloud->id, $completionexpected);

    return true;
}

/**
 * Add a get_coursemodule_info function in case any wordcloud type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function wordcloud_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, course, name, intro, introformat, timeopen, timeclose, completionsubmits';
    if (! $wordcloud = $DB->get_record('wordcloud', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $wordcloud->name;

    if ($coursemodule->showdescription) {
        $result->content = format_module_intro('wordcloud', $wordcloud, $coursemodule->id, false);
    }

    // Populate some other values that can be used in calendar or on dashboard.
    if ($wordcloud->timeopen) {
        $result->customdata['timeopen'] = $wordcloud->timeopen;
    }
    if ($wordcloud->timeclose) {
        $result->customdata['timeclose'] = $wordcloud->timeclose;
    }

    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionsubmits'] = $wordcloud->completionsubmits;
    }

    return $result;
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settingsnav The settings navigation object
 * @param navigation_node $wordcloudnode The node to add module settings to
 */
function wordcloud_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $wordcloudnode) {
    if (has_capability('mod/wordcloud:editentry', $settingsnav->get_page()->context)) {
        $url = new moodle_url('/mod/wordcloud/wordlist.php', ['id' => $settingsnav->get_page()->cm->id]);
        $wordcloudnode->add(get_string('wordlist', 'mod_wordcloud'), $url, navigation_node::TYPE_SETTING, null, 'mod_wordcloud_list');
    }
}

/**
 * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
 *
 * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
function mod_wordcloud_get_completion_active_rule_descriptions($cm) {
    // Values will be present in cm_info, and we assume these are up to date.
    if (
        empty($cm->customdata['customcompletionrules'])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC
    ) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionsubmits':
                if (!empty($val)) {
                    $descriptions[] = get_string('completionpostsdesc', 'forum', $val);
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}

/**
 * Helper to package JSON settings.
 * @param object $data
 */
function mod_wordcloud_package_settings(&$data) {
    $settings = [
        'usemonocolor'  => $data->usemonocolor ?? 0,
        'monocolor'     => $data->monocolor ?? 1,
        'monocolorhex'  => $data->monocolorhex ?? '000000',
        'renderstyle'   => $data->renderstyle ?? 0,
        'font'          => $data->font ?? '',
        'textalignment' => $data->textalignment ?? '',
    ];
    $data->rendersettings = json_encode($settings);
}

/**
 * Returns the list of web safe fonts available for rendering the wordcloud.
 *
 * @return array list of font CSS values indexed keyed by their display label.
 */
function mod_wordcloud_get_render_fonts() {
    return [
        'Arial, sans-serif' => 'Arial (sans-serif)',
        'Verdana, sans-serif' => 'Verdana (sans-serif)',
        'Tahoma, sans-serif' => 'Tahoma (sans-serif)',
        'Times New Roman, serif' => 'Times New Roman (serif)',
        'Georgia, serif' => 'Georgia (serif)',
        'Courier New, monospace' => 'Courier New (monospace)',
        'Brush Script MT, cursive' => 'Brush Script MT (cursive)',
    ];
}

/**
 * Returns the list of available text alignments for rendering the wordcloud.
 *
 * @return array list of text alignment labels keyed by their internal code.
 */
function mod_wordcloud_get_render_textalignments() {
    return [
        'hvd' => get_string('textalignmenthvd', 'wordcloud'),
        'hv' => get_string('textalignmenthv', 'wordcloud'),
        'h' => get_string('textalignmenth', 'wordcloud'),
        'v' => get_string('textalignmentv', 'wordcloud'),
    ];
}

/**
 * Returns the render style to use for the given text alignment and font.
 *
 * The classic (CSS-based) renderer can only lay words out horizontally and
 * cannot apply a font, so it is used only when the activity asks for horizontal
 * text in the one font it is able to show (see WORDCLOUD_CLASSIC_FONT). Every
 * other combination is drawn by the modern, canvas-based renderer, which
 * honours both settings.
 *
 * @param string $textalignment the text alignment code.
 * @param string|null $font the font CSS value, as returned by mod_wordcloud_get_render_fonts().
 * @return int 0 for the classic renderer, 1 for the modern one.
 */
function mod_wordcloud_get_render_style($textalignment, $font = null) {
    if ($textalignment == 'h' && $font === WORDCLOUD_CLASSIC_FONT) {
        return 0;
    }

    return 1;
}

/**
 * Callback to fetch the social/actionable link for the timeline block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $userid
 * @return action_data|null
 */
function mod_wordcloud_core_calendar_provide_event_action(
    calendar_event $event,
    \core_calendar\action_factory $factory,
    int $userid = 0
) {
    global $DB, $USER;

    if (!$userid) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['wordcloud'][$event->instance];

    if (!$cm->uservisible) {
        return null;
    }

    $completion = new completion_info($cm->get_course());
    $completiondata = $completion->get_data($cm);

    if ($completiondata->completionstate == COMPLETION_COMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new \moodle_url('/mod/wordcloud/view.php', ['id' => $cm->id]),
        1,
        true
    );
}
