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

defined('MOODLE_INTERNAL') || die;

// Backward compatibility with Moodle 3.11. Remove, when EOL.
if (!defined('FEATURE_MOD_PURPOSE')) {
    define('FEATURE_MOD_PURPOSE', 'mod_purpose');
}
if (!defined('MOD_PURPOSE_COLLABORATION')) {
    define('MOD_PURPOSE_COLLABORATION', 'collaboration');
}

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

    $dbparams = array('id' => $coursemodule->instance);
    $fields = 'id, course, name, intro, introformat, timeopen, timeclose';
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

    return $result;
}
