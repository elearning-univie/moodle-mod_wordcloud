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
 * Index
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

defined(MOODLE_INTERNAL) || die();

$id = required_param('id', PARAM_INT);   // Course id.

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('Course ID is incorrect');
}
$coursecontext = context_course::instance($course->id);

require_course_login($course);

$event = \mod_wordcloud\event\course_module_instance_list_viewed::create(array(
        'context' => $coursecontext
));
$event->trigger();

$PAGE->set_url('/mod/wordcloud/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();
$strwordclouds = get_string("modulenameplural", "wordcloud");

if (!$wordclouds = get_all_instances_in_course('wordcloud', $course)) {
    notice(get_string('thereareno', 'moodle', $strwordclouds), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$table = new html_table();
if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'), get_string('name'));
    $table->align = array('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'), get_string('name'));
    $table->align = array('center', 'left', 'left', 'left');
} else {
    $table->head  = array(get_string('name'));
    $table->align = array('left', 'left', 'left');
}

foreach ($wordclouds as $wordcloud) {
    if (!$wordcloud->visible) {
        $link = html_writer::link(
                new moodle_url('/mod/wordcloud/view.php', array('id' => $wordcloud->coursemodule)),
                format_string($wordcloud->name, true),
                array('class' => 'dimmed'));
    } else {
        $link = html_writer::link(
                new moodle_url('/mod/wordcloud/view.php', array('id' => $wordcloud->coursemodule)),
                format_string($wordcloud->name, true));
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array($wordcloud->section, $link);
    } else {
        $table->data[] = array($link);
    }
}

echo $OUTPUT->heading($strwordclouds, 2);
echo html_writer::table($table);
echo $OUTPUT->footer();