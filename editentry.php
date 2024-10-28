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
 * Wordcloud edit word entries
 *
 * @package    mod_wordcloud
 * @copyright  2022 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

global $PAGE, $OUTPUT, $DB;

$id = required_param('id', PARAM_INT);
$deleteselected = optional_param('deleteselected', null, PARAM_INT);
$confirm = optional_param('confirm', null, PARAM_ALPHANUM);
$perpage = optional_param('perpage', 20, PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'wordcloud');
$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/wordcloud:editentry', $context);

$params = [];
$params['id'] = $id;
if ($perpage) {
    $params['perpage'] = $perpage;
}

$wordcloud = $DB->get_record('wordcloud', ['id' => $cm->instance]);

$PAGE->set_url(new moodle_url("/mod/wordcloud/editentry.php", $params));
$node = $PAGE->settingsnav->find('mod_wordcloud', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$pagetitle = get_string('pagetitle', 'wordcloud');
$PAGE->set_title($wordcloud->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');
$activityheader = $PAGE->activityheader;
$activityheader->set_attrs([
    'description' => '',
    'hidecompletion' => true,
]);

if ($deleteselected) {
    if (!$DB->record_exists('wordcloud_map', ['id' => $deleteselected])) {
        redirect($PAGE->url);
    }

    if ($confirm == md5($deleteselected)) {
        $DB->delete_records('wordcloud_map', ['id' => $deleteselected]);
        $DB->delete_records('wordcloud_word_user_rel', ['mapid' => $deleteselected]);
        redirect($PAGE->url);
    } else {
        $deleteurl = new moodle_url('/mod/wordcloud/editentry.php',
            ['id' => $id, 'deleteselected' => $deleteselected, 'sesskey' => sesskey(), 'confirm' => md5($deleteselected)]);

        $continue = new \single_button($deleteurl, get_string('remove', 'moodle'), 'post');
        $word = '<strong>' . $DB->get_field('wordcloud_map', 'word', ['id' => $deleteselected]) . '</strong>';

        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('removewordcheck', 'mod_wordcloud', $word), $continue, $PAGE->url);
        echo $OUTPUT->footer();
        die();
    }
}

$table = new mod_wordcloud\output\editentrytable('uniqueid', $cm->id);

$groupmode = groups_get_activity_groupmode($cm);
$groupid = $groupmode ? groups_get_activity_group($cm) : 0;
if ($groupmode && $groupid === 0) {
    $groupid = -1;
}

$sqlwhere = "wordcloudid = $wordcloud->id AND groupid = $groupid";
$table->set_sql("*", "{wordcloud_map}", $sqlwhere);
$table->define_baseurl($PAGE->url);

$backurl = new moodle_url("/mod/wordcloud/view.php", $params);

echo $OUTPUT->header();
echo html_writer::tag('button', get_string('save', 'moodle'), [
    'id' => 'mod-wordcloud-update-btn',
    'class' => 'btn btn-primary',
    'data-aid' => "$wordcloud->id",
    'data-backurl' => "$backurl",
]);
echo '    ';
echo html_writer::tag('button', get_string('cancel', 'moodle'),
    ['class' => 'btn btn-secondary', 'onclick' => "location.href='$backurl'"]);
$PAGE->requires->js_call_amd('mod_wordcloud/editword', 'init');

echo html_writer::start_div('tablewidth');
$table->out(20, false);
echo html_writer::end_div();
echo $OUTPUT->footer();
