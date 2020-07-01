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

global $PAGE, $OUTPUT, $COURSE, $USER, $DB;

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

/*if (has_capability('mod/flashcards:studentview', $context) ) {
    $redirecturl = new moodle_url('/mod/flashcards/studentview.php', array('id' => $id));
    redirect($redirecturl);
}*/

echo $OUTPUT->header();
echo $OUTPUT->heading($wordcloud->name);


//wörter generator block
$sql = 'SELECT sum(count) 
          FROM {wordcloud_map} 
         WHERE wordcloudid = :wordcloudid';
$wordcnt = $DB->get_field_sql($sql, ['wordcloudid' => $wordcloud->id]);

$records = $DB->get_records('wordcloud_map',['wordcloudid' => $wordcloud->id]);
$cloudhtml = '';
foreach ($records as $row) {
    $cloudhtml .= "<p>$row->word</p>";
}
$templatecontext['cloudhtml'] = $cloudhtml;
//wörter generator block ende


$renderer = $PAGE->get_renderer('core');
echo $renderer->render_from_template('mod_wordcloud/wordcloud', $templatecontext);
echo $OUTPUT->footer();
