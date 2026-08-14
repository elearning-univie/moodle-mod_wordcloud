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
    $settings->add(new admin_setting_configtext(
        'wordcloud/refresh',
        get_string('refreshtime', 'wordcloud'),
        get_string('refreshtimedesc', 'wordcloud'),
        5,
        PARAM_INT
    ));

    $colors = ['0063A6', '11897A', '94C154', 'F6A800', 'DD4814', 'A71C49'];

    for ($i = 1; $i <= count($colors); $i++) {
        $settingname = 'wordcloud/fontcolor' . $i;
        $visiblename = get_string('fontcolor', 'wordcloud', $i);
        $description = get_string('fontcolordesc', 'wordcloud', $i);
        $settings->add(new admin_setting_configtext($settingname, $visiblename, $description, $colors[$i - 1], PARAM_ALPHANUM));
    }

    $settings->add(new admin_setting_configtext(
        'wordcloud/refresh',
        get_string('refreshtime', 'wordcloud'),
        get_string('refreshtimedesc', 'wordcloud'),
        5,
        PARAM_INT
    ));

    $settings->add(new admin_setting_heading('fontoptions', get_string('fontandoptions', 'wordcloud'), ''));

    $fonts = mod_wordcloud_get_render_fonts();

    $settings->add(new admin_setting_configselect(
        'wordcloud/defaultfont',
        new lang_string('defaultfont', 'wordcloud'),
        new lang_string('defaultfontdesc', 'wordcloud'),
        'Arial, sans-serif',
        $fonts
    ));

    $textalignment = mod_wordcloud_get_render_textalignments();

    $settings->add(new admin_setting_configselect(
        'wordcloud/defaulttextalignment',
        new lang_string('defaulttextalignment', 'wordcloud'),
        new lang_string('defaulttextalignmentdesc', 'wordcloud'),
        'h',
        $textalignment
    ));

    $settings->add(new admin_setting_heading('furtheroptions', get_string('furtheroptions', 'wordcloud'), ''));

    $defaultrendersettings = json_encode([
        'gridSize' => 8,
        'color' => 'random-dark',
        'rotateRatio' => 0.5,
        'backgroundColor' => '#ffffff',
        "shrinkToFit" => true,
        "drawOutOfBound" => false,
        "minSize" => 1,
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    $settings->add(new admin_setting_configtextarea(
        'wordcloud/rendersettings',
        get_string('rendersettings', 'wordcloud'),
        get_string('rendersettingsdesc', 'wordcloud'),
        $defaultrendersettings,
        PARAM_RAW
    ));
}
