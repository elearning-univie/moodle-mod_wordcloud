<?php
// This file is part of the Choice group module for Moodle - http://moodle.org/
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
 * Mobile app config
 * @package    mod_wordcloud
 * @copyright  2022 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$addons = [
    'mod_wordcloud' => [
        'handlers' => [
            'coursewordcloud' => [
                'delegate' => 'CoreCourseModuleDelegate',
                'method' => 'mobile_wordcloud_view',
                'displaydata' => [
                    'title' => 'pluginname',
                    'icon' => $CFG->wwwroot . '/mod/wordcloud/pix/icon.svg',
                ],
                'styles' => [
                    'url' => $CFG->wwwroot . '/mod/wordcloud/mobile/mobile_styles.css',
                    'version' => '1.0',
                ],
            ],
        ],
        'lang' => [
            ['close', 'wordcloud'],
            ['open', 'wordcloud'],
            ['pluginname', 'wordcloud'],
            ['refreshtext', 'wordcloud'],
            ['selectagroup', 'moodle'],
            ['submitbtn', 'wordcloud'],
            ['textbox', 'wordcloud'],
        ],
    ],
];
