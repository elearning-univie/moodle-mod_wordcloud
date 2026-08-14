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
 * English lang strings
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['activityclose'] = 'Due date';
$string['activityclose_help'] = 'If enabled, students will not be able to submit words after this date. If disabled, a submission is possible at any time.';
$string['activityopen'] = 'Allow submissions from';
$string['activityopen_help'] = 'If enabled, students will not be able to submit words before this date. If disabled, a submission is possible right away.';
$string['appearance'] = 'Appearance';
$string['classicview'] = 'Classic view';
$string['close'] = 'Closes: ';
$string['closebeforeopen'] = 'Could not update the wordcloud. You have specified a close date before the open date.';
$string['cloud'] = 'Wordcloud';
$string['cloudvisibility'] = 'Visibility of submitted words';
$string['cloudvisibility_help'] = 'Here you can define how words of the wordcloud should be displayed for participants:<ul><li>At any time. - Words are visible at any time and without any restrictions.</li><li>Only after own submission. - Words are only visible after the participant has submitted at least 1 word.</li><li>Only after due date. - Words are only visible when the due date has passed.</li></ul>';
$string['color_custom'] = 'Custom';
$string['completiondetail:submits'] = 'Submit words: {$a}';
$string['completionsubmits'] = 'Student must submit at least the following number of words:';
$string['completionsubmitsgroup'] = 'Require submission';
$string['count'] = 'Count';
$string['defaultfont'] = 'Default font';
$string['defaultfontdesc'] = 'Set the default font for new modules.';
$string['defaulttextalignment'] = 'Default text alignment';
$string['defaulttextalignmentdesc'] = 'Set the default text alignment for new modules.';
$string['defaultwordcloudstyle'] = 'Default wordcloud style';
$string['defaultwordcloudstyledesc'] = 'Set the default wordcloud style for new modules';
$string['display'] = 'Display';
$string['editentry'] = 'Edit words';
$string['error_notab'] = 'New tab was not opened';
$string['errormonocolorhex'] = 'Value is not a hexadecimal representation of a color!';
$string['errortoomanywords'] = 'Max amount of words reached!';
$string['errorupdateentries'] = 'The following words could not be updated: ';
$string['errorwordoverflow'] = 'New entry is too long!';
$string['export'] = 'Export as ... ';
$string['export_content_mismatch'] = 'Expected to find "{$a}" in the downloaded file, but it was not there.';
$string['exportcsv'] = 'Export CSV';
$string['exportdefault'] = 'Export';
$string['exportpng'] = 'Export PNG';
$string['font'] = 'Font';
$string['font_help'] = 'Select the font you want to use to display the wordcloud. All fonts listed here are considered as web safe and can therefore be displayed in the same way on different browsers and devices.';
$string['fontcolor'] = 'Textcolor {$a}';
$string['fontcolordesc'] = 'Set the textcolor {$a}. Define the color values as hexcode. The words will only be colored when all textcolors are set. ';
$string['fontandoptions'] = 'Font and text options';
$string['fullscreen'] = 'Fullscreen';
$string['furtheroptions'] = 'Further options';
$string['list'] = 'List';
$string['modulename'] = 'Wordcloud';
$string['modulename_help'] = 'This module allows teachers to visualize a list of tags as a word cloud. The importance of a tag is shown with fontsize, according to the number of mentions.';
$string['modulenameplural'] = 'Wordclouds';
$string['monocolor'] = 'Base color';
$string['monocolor_help'] = 'Choose one of the colors defined in the admin settings for the mono-colored color scheme.';
$string['monocolor_hex'] = 'Custom color';
$string['monocolor_hex_help'] = 'Please fill in a six-digit hexadecimal value of the desired monocolor in the form "aabb11" (without the "#").';
$string['monocolordesc'] = 'Set one color for the wordcloud words. The system then calculates 6 colors with different lightness value. Define the color values as hexcode without #.';
$string['newview'] = 'Modern view';
$string['notification'] = 'Please select a group to submit a word';
$string['open'] = 'Opens: ';
$string['pagetitle'] = 'Moodle Wordcloud';
$string['pluginadministration'] = 'Wordcloud administration';
$string['pluginname'] = 'Wordcloud';
$string['privacy:metadata'] = 'The wordcloud plugin does not store any personal data.';
$string['referlist'] = 'For an accessible view of submitted words, please select the option &quot;List&quot; in the drop-down list &quot;Display&quot;.';
$string['refreshtext'] = 'Refresh the page to get the latest wordcloud content';
$string['refreshtime'] = 'Refresh time';
$string['refreshtimedesc'] = 'Set the auto refresh interval time in seconds';
$string['removewordcheck'] = 'Are you sure you want to remove the word \'{$a}\' from this wordcloud activity?';
$string['rendersettings'] = 'Further style options';
$string['rendersettingsdesc'] = 'Set further style options for the appearance of wordclouds for new and existing modules.';
$string['renderstyle'] = 'Render style';
$string['saved'] = 'Saved';
$string['submitbtn'] = 'Submit';
$string['submittedwords'] = 'Submitted words';
$string['textalignment'] = 'Text alignment';
$string['textalignment_help'] = 'Select the text alignment you want to use to display the words of the wordcloud.';
$string['textalignmenth'] = 'Horizontal only';
$string['textalignmenthv'] = 'Horizontal & vertical';
$string['textalignmenthvd'] = 'Horizontal, vertical & diagonal';
$string['textalignmentv'] = 'Vertical only';
$string['textbox'] = 'Add a word here...';
$string['timing'] = 'Availability';
$string['url_mismatch'] = 'Incorrect URL';
$string['usemonocolor'] = 'Color scheme';
$string['usemonocolor_help'] = 'You can choose here between a random coloring or a sequentially shaded palette. <br /><em>Random coloring</em>: Your words get a random color. Words mentioned in the same or similar frequency are shown in the same color. <br /><em>Sequentially shaded palette</em>: Here you can choose one of the provided colors or enter a custom color in six-digit hexadecial value of your own for a monochrome color scheme. All the words will have the same color, words with a different word count will get a brighter or darker luminosity.';
$string['usemonocolor_random'] = 'random coloring';
$string['usemonocolor_sequential'] = 'sequentially shaded palette';
$string['userinfosubmit'] = 'Words are displayed only after submission!';
$string['userinfotime'] = 'Words are displayed only after the submission deadline!';
$string['visibilityalways'] = 'At any time';
$string['visibilitysubmit'] = 'Only after own submission';
$string['visibilitytime'] = 'Only after due date';
$string['visibilitytimeerror'] = 'This option can only be used with an defined due date.';
$string['warning'] = 'Warning';
$string['warningdoubleentries'] = 'Identical entries found in the list. If you continue these will be merged together and the counts will be added up.<br /><br />Following entries are not unique: ';
$string['word'] = 'Word';
$string['wordcloud:addinstance'] = 'Create wordcloud activity';
$string['wordcloud:editentry'] = 'Edit words';
$string['wordcloud:submit'] = 'Submit new words';
$string['wordcloud:view'] = 'View the wordcloud';
$string['wordcloudname'] = 'Wordcloud activity name';
$string['wordlist'] = 'Wordlist';
