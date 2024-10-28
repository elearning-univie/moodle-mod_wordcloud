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
 * Wordlist view
 *
 * @package    mod_wordcloud
 * @copyright  2023 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');

global $PAGE, $OUTPUT, $DB, $CFG;

$id = required_param('id', PARAM_INT);
$listview = optional_param('listview', 0, PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'wordcloud');

$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/wordcloud:editentry', $context);

$wordcloud = $DB->get_record('wordcloud', ['id' => $cm->instance]);

$PAGE->set_url(new moodle_url("/mod/wordcloud/wordlist.php", ['id' => $id]));
$node = $PAGE->settingsnav->find('mod_wordcloud_list', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$PAGE->navbar->add(
    get_string('yes'),
    new moodle_url('/a/link/if/you/want/one.php')
);

$pagetitle = get_string('pagetitle', 'wordcloud');
$PAGE->set_title($wordcloud->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');
$activityheader = $PAGE->activityheader;
$activityheader->set_attrs([
    'description' => '',
    'hidecompletion' => true,
]);

$wordcloudconfig = get_config('wordcloud');

$groupmode = groups_get_activity_groupmode($cm);
$groupid = $groupmode ? groups_get_activity_group($cm, true) : 0;

$params = [
    'objectid' => $cm->id,
    'context' => $context,
];

$event = \mod_wordcloud\event\course_module_viewed::create($params);
$event->add_record_snapshot('wordcloud', $wordcloud);
$event->trigger();

$renderer = $PAGE->get_renderer('core');

$listrecords = $DB->get_records_sql('SELECT word, count FROM {wordcloud_map} WHERE wordcloudid = :wordcloudid AND groupid = :groupid',
    ['wordcloudid' => $wordcloud->id, 'groupid' => $groupid]);

$wordcount = $DB->get_record_sql('SELECT sum(count) AS count FROM {wordcloud_map} WHERE wordcloudid = :wordcloudid AND groupid = :groupid',
    ['wordcloudid' => $wordcloud->id, 'groupid' => $groupid]);

echo $renderer->header();
echo html_writer::tag('button', get_string('editentry', 'mod_wordcloud'),
    ['class' => 'btn btn-primary', 'onclick' => "location.href='" . new moodle_url("/mod/wordcloud/editentry.php", ['id' => $id]) . "'"]);
$exporturl = new moodle_url("/mod/wordcloud/export.php", ['id' => $id]);
$exportmenu['0'] = get_string('exportdefault', 'mod_wordcloud');
$exportmenu[$exporturl->out()] = get_string('exportcsv', 'mod_wordcloud');
$exportmenu['png'] = get_string('exportpng', 'mod_wordcloud');
echo html_writer::start_tag('div', ['id' => 'mod-wordcloud-list-export']);
echo html_writer::label(get_string('export', 'mod_wordcloud'), 'mod-wordcloud-export-menu');
echo html_writer::select($exportmenu, 'testname', '0', 0, ['id' => 'mod-wordcloud-export-menu']);
echo html_writer::end_div();

if ($groupmode) {
    $groupselecturl = new moodle_url('/mod/wordcloud/wordlist.php', ['id' => $cm->id]);
    groups_print_activity_menu($cm, $groupselecturl);
}

$PAGE->requires->js_call_amd('mod_wordcloud/uicontroller', 'initlistener');
$PAGE->requires->js_call_amd('mod_wordcloud/config');
$PAGE->requires->js_call_amd('mod_wordcloud/exportpng', 'init', [$wordcloud->name]);

echo html_writer::start_div('', ['id' => 'mod-wordcloud-words-box']);
echo $renderer->render_from_template('mod_wordcloud/wordlist', ['words' => array_values($listrecords)]);
echo html_writer::end_div();
echo $renderer->footer();
