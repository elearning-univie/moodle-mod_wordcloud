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
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');

global $PAGE, $OUTPUT, $DB, $CFG;

$id = required_param('id', PARAM_INT);
$listview = optional_param('listview', 0, PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'wordcloud');

$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/wordcloud:view', $context);

$wordcloud = $DB->get_record('wordcloud', ['id' => $cm->instance]);

$PAGE->set_url(new moodle_url("/mod/wordcloud/view.php", ['id' => $id]));
$node = $PAGE->settingsnav->find('mod_wordcloud', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}

$pagetitle = get_string('pagetitle', 'wordcloud');
$PAGE->set_title($wordcloud->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('limitedwidth');
$wordcloudconfig = get_config('wordcloud');

if ($wordcloud->usemonocolor) {
    if ($wordcloud->monocolor == 0) {
        $colors[] = '#' . $wordcloud->monocolorhex;
    } else {
        $fontcolor = 'fontcolor' . $wordcloud->monocolor;
        $colors[] = '#' . $wordcloudconfig->$fontcolor;
    }
} else {
    // 1 to 6 to match the wordcloud text css classes.
    for ($i = 1; $i <= 6; $i++) {
        $fontcolor = 'fontcolor' . $i;
        $colors[] = $wordcloudconfig->$fontcolor;
    }
}

$groupmode = groups_get_activity_groupmode($cm);
$groupid = $groupmode ? groups_get_activity_group($cm, true) : 0;

$cansubmit = mod_wordcloud_can_submit($wordcloud, $context, $groupid);

$templatecontext = [
    'timeopen' => $cansubmit['timeopen'],
    'timeclose' => $cansubmit['timeclose'],
    'timing' => $cansubmit['timing'],
    'writeaccess' => $cansubmit['writeaccess'],
    'wordcloudname' => $wordcloud->name,
    'exportlink' => new moodle_url("/mod/wordcloud/export.php", ['id' => $id]),
    'colors' => $colors,
];

$canedit = false;

if (has_capability('mod/wordcloud:editentry', $context) && !($groupmode && $groupid === 0)) {
    $templatecontext['editlink'] = new moodle_url("/mod/wordcloud/editentry.php", ['id' => $id]);
    $canedit = true;
}

if ($templatecontext['writeaccess']) {
    $PAGE->requires->js_call_amd('mod_wordcloud/addwordtowordcloud', 'init', [$wordcloudconfig->refresh, $wordcloud->id, time(), $listview]);
}

$views = [
    [
        'url' => $PAGE->url . "&listview=0",
        'text' => get_string('cloud', 'mod_wordcloud'),
    ],
    [
        'url' => $PAGE->url . "&listview=1",
        'text' => get_string('list', 'mod_wordcloud'),
    ],
];

$params = [
    'objectid' => $cm->id,
    'context' => $context,
];

$event = \mod_wordcloud\event\course_module_viewed::create($params);
$event->add_record_snapshot('wordcloud', $wordcloud);
$event->trigger();

$views[$listview]['selected'] = 1;
$cloudhtml = mod_wordcloud_get_cloudhtml($wordcloud->id, $groupmode, $groupid, $listview, $canedit);
$templatecontext['cloudhtml'] = $cloudhtml['cloudhtml'];
$templatecontext['views'] = $views;
$templatecontext['wordcount'] = $cloudhtml['sumcount'];
if ($cloudhtml['sumcount'] == 0) {
    $templatecontext['disabled'] = 1;
}

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$renderer = $PAGE->get_renderer('core');
echo $renderer->header();

if ($groupmode) {
    $groupselecturl = new moodle_url('/mod/wordcloud/view.php', ['id' => $cm->id]);
    groups_print_activity_menu($cm, $groupselecturl);
    if ($groupid == 0) {
        $templatecontext['notification'] = get_string('notification', 'mod_wordcloud');
    }
}

$PAGE->requires->js_call_amd('mod_wordcloud/uicontroller', 'init', [$colors]);
$PAGE->requires->js_call_amd('mod_wordcloud/config');
$PAGE->requires->js_call_amd('mod_wordcloud/exportpng', 'init', [$wordcloud->name]);
echo $renderer->render_from_template('mod_wordcloud/wordcloud', $templatecontext);
echo $renderer->footer();
