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
 * Wordcloud mobile view
 *
 * @package    mod_wordcloud
 * @copyright  2022 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_wordcloud\output;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../locallib.php');

/**
 * Wordcloud mobile view class
 *
 * @package mod_wordcloud
 * @copyright 2022 University of Vienna
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {
    /**
     * Returns the wordcloud view for the mobile app.
     *
     * @param array $args Arguments from tool_mobile_get_content WS
     * @return array HTML, javascript and otherdata
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \require_login_exception
     * @throws \required_capability_exception
     */
    public static function mobile_wordcloud_view($args) {
        global $OUTPUT, $USER, $DB, $CFG;

        $args = (object) $args;
        $versionname = $args->appversioncode >= 44000 ? 'latest' : 'ionic5';
        $cm = get_coursemodule_from_id('wordcloud', $args->cmid);
        $context = \context_module::instance($cm->id);

        require_login($cm->course, false, $cm, true, true);
        require_capability('mod/wordcloud:view', $context);

        if ($args->userid != $USER->id) {
            require_capability('mod/wordcloud:manage', $context);
        }

        $groupid = empty($args->group) ? 0 : $args->group;
        $groupmode = groups_get_activity_groupmode($cm);

        $moodle4 = ($CFG->version >= 2022041900) ? true : false;
        $wordcloud = $DB->get_record('wordcloud', ['id' => $cm->instance]);
        $cloudhtml = mod_wordcloud_get_cloudhtml($wordcloud->id, $groupmode, $groupid);
        $wordcloudconfig = get_config('wordcloud');
        $colors = '';

        $cansubmit = mod_wordcloud_can_submit($wordcloud, $context, $groupid);

        if ($wordcloud->usemonocolor) {
            if ($wordcloud->monocolor == 0) {
                $colors = '#' . $wordcloud->monocolorhex;
            } else {
                $fontcolor = 'fontcolor' . $wordcloud->monocolor;
                $colors = '#' . $wordcloudconfig->$fontcolor;
            }
        } else {
            for ($i = 1; $i <= 6; $i++) {
                $fontcolor = 'fontcolor' . $i;
                $colors .= '.w' . $i . ' {color: #' . $wordcloudconfig->$fontcolor . ';} ';
            }
        }

        $data = [
            'wordcloud' => $wordcloud,
            'cmid' => $cm->id,
            'writeaccess' => $cansubmit['writeaccess'],
            'timing' => $cansubmit['timing'],
            'timeopen' => $cansubmit['timeopen'],
            'timeclose' => $cansubmit['timeclose'],
        ];

        if ($groupmode) {
            $groups = self::get_groups($context, $cm, $groupmode, $groupid);
            $data['showgroups'] = !empty($groups);
            $data['groups'] = array_values($groups);
        } else {
            $data['showgroups'] = false;
        }

        if ($moodle4) {
            $data['timing'] = null;
        }

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template("mod_wordcloud/mobile_view_page_$versionname", $data),
                ],
            ],
            'javascript' => file_get_contents($CFG->dirroot . '/mod/wordcloud/mobile/mobile_uicontroller.js'),
            'otherdata' => ['cloudhtml' => $cloudhtml['cloudhtml'], 'word' => '', 'colors' => $colors, 'group' => $groupid]
        ];
    }

    /**
     * Returns an array of groups to be displayed and updates the active group in the session.
     *
     * @param \context $context Context
     * @param \stdClass $cm The course module
     * @param int $groupmode The group mode
     * @param int $groupid The group id
     * @return array The array of groups, may be empty.
     */
    private static function get_groups($context, $cm, $groupmode, $groupid) {
        global $SESSION;

        $arrgroups = [];
        if ($groups = groups_get_activity_allowed_groups($cm)) {
            if ($groupmode == VISIBLEGROUPS || has_capability('moodle/site:accessallgroups', $context)) {
                $allparticipants = new \stdClass();
                $allparticipants->id = 0;
                $allparticipants->name = get_string('allparticipants');
                $allparticipants->selected = $groupid === 0;
                $arrgroups[0] = $allparticipants;
            }

            if ($groups && array_key_exists($groupid, $groups)) {
                $SESSION->activegroup[$cm->course][$groupmode][$cm->groupingid] = $groupid;
            }

            foreach ($groups as $gid => $group) {
                $group->selected = $gid == $groupid;
                $arrgroups[] = $group;
            }
        }
        return $arrgroups;
    }
}
