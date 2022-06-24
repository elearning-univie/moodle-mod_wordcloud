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
 * Admin settings of the wordcloud plugin
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('wordcloud/refresh', get_string('refreshtime', 'wordcloud'),
        get_string('refreshtimedesc', 'wordcloud'), 5, PARAM_INT));

    $colors = ['0063A6', '11897A', '94C154', 'F6A800', 'DD4814', 'A71C49'];

    for ($i = 1; $i <= count($colors); $i++) {
        $settingname = 'wordcloud/fontcolor' . $i;
        $visiblename = get_string('fontcolor', 'wordcloud', $i);
        $description = get_string('fontcolordesc', 'wordcloud', $i);
        $settings->add(new admin_setting_configtext($settingname, $visiblename, $description, $colors[$i - 1], PARAM_ALPHANUM));
    }
}