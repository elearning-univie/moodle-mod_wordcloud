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
 * Returns the information on whether the module supports a feature
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function wordcloud_supports($feature) {
    switch($feature) {
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

    return $DB->insert_record('wordcloud', $wordcloud);
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

    return $DB->update_record('wordcloud', $wordcloud);
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
    if (empty($cm->customdata['customcompletionrules'])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
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
