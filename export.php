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
 * Wordcloud export page
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');

global $PAGE, $OUTPUT, $DB;

$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'wordcloud');

$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/wordcloud:view', $context);

$wordcloud = $DB->get_record('wordcloud', ['id' => $cm->instance]);
$groupmode = groups_get_activity_groupmode($cm);
$groupid = $groupmode ? groups_get_activity_group($cm) : 0;

$PAGE->set_url(new moodle_url("/mod/wordcloud/export.php", ['id' => $id]));
$node = $PAGE->settingsnav->find('mod_wordcloud', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$pagetitle = get_string('pagetitle', 'wordcloud');
$PAGE->set_title($wordcloud->name);
$PAGE->set_heading($course->shortname);

if ($groupmode && $groupid === 0) {
    $sql = 'SELECT word, sum(count) AS count
              FROM {wordcloud_map}
             WHERE wordcloudid = :wordcloudid
               AND groupid != 0
          GROUP BY word';
    $records = $DB->get_records_sql($sql, ['wordcloudid' => $wordcloud->id]);
} else {
    $records = $DB->get_records('wordcloud_map', ['wordcloudid' => $wordcloud->id, 'groupid' => $groupid]);
}

$csvexport = new csv_export_writer('semicolon');
$filename = get_string('pluginname', 'mod_wordcloud');
$filename .= clean_filename('-' . gmdate("Ymd_Hi")) . '.csv';
$csvexport->filename = $filename;
$csvexport->add_data([get_string('word', 'mod_wordcloud'), get_string('count', 'mod_wordcloud')]);

foreach ($records as $record) {
    $word = str_replace(';', ',', $record->word);
    $csvexport->add_data([$word, $record->count]);
}
$csvexport->download_file();
die();
