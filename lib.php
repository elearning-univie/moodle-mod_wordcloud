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

/**
 * wordcloud_add_instance
 *
 * @param array $wordcloud
 * @return bool
 */
function wordcloud_add_instance($wordcloud) {
    global $COURSE, $DB;

    $wordclouddb = new stdClass();
    $wordclouddb->course = $COURSE->id;
    $wordclouddb->name = $wordcloud->name;

    $id = $DB->insert_record('wordcloud', $wordclouddb);
    return $id;
}

/**
 * wordcloud_delete_instance
 *
 * @param int $id
 * @return bool
 */
function wordcloud_delete_instance(int $id) {
    global $DB;

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
    global $COURSE, $DB;

    $wordclouddb = new stdClass();
    $wordclouddb->course = $COURSE->id;
    $wordclouddb->name = $wordcloud->name;
    
    return $DB->update_record('wordcloud', $wordclouddb);
}