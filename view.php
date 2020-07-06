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
 * Wordcloud view
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once('lib.php');

global $PAGE, $OUTPUT, $DB;

$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'wordcloud');

$context = context_module::instance($cm->id);

require_login($course, false, $cm);

$wordcloud = $DB->get_record('wordcloud', array('id' => $cm->instance));

$PAGE->set_url(new moodle_url("/mod/wordcloud/view.php", ['id' => $id]));
$node = $PAGE->settingsnav->find('mod_wordcloud', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$pagetitle = get_string('pagetitle', 'wordcloud');
$PAGE->set_title($wordcloud->name);
$PAGE->set_heading($course->shortname);

if (!has_capability('mod/wordcloud:view', $context) ) {
    echo $OUTPUT->heading(get_string('errornotallowedonpage', 'flashcards'));
    echo $OUTPUT->footer();
    die();
}

echo $OUTPUT->header();
$PAGE->requires->js_call_amd('mod_wordcloud/addwordtowordcloud', 'init');

$sql = 'SELECT min(count) as mincount, max(count) as maxcount
          FROM {wordcloud_map} 
         WHERE wordcloudid = :wordcloudid';
$wordcnt = $DB->get_record_sql($sql, ['wordcloudid' => $wordcloud->id]);

$records = $DB->get_records('wordcloud_map',['wordcloudid' => $wordcloud->id]);
$cloudhtml = '';

$range = max(.01, $wordcnt->maxcount - $wordcnt->mincount) * 1.0001;
if ($range >= 6) {
    $steps = 6;
} else {
    $steps = 1;
}

foreach ($records as $row) {
    $weight = 1 + floor($steps * ($row->count - $wordcnt->mincount) / $range);
    $fontsize = 12 + 6 * $weight;
    $cloudhtml .= "<span class='mod_wordcloud_word mod-wordcloud-center' style='font-size: " . $fontsize . "px;' title='$row->count'>$row->word</span>";
}

$templatecontext['heading'] = $wordcloud->name;
$templatecontext['cloudhtml'] = $cloudhtml;
$templatecontext['aid'] = $wordcloud->id;

$renderer = $PAGE->get_renderer('core');
echo $renderer->render_from_template('mod_wordcloud/wordcloud', $templatecontext);
echo $OUTPUT->footer();
