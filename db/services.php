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
 * External service definition
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$services = array(
    'wordcloudservice' => array(
        'functions' => array('mod_wordcloud_add_word', 'mod_wordcloud_get_words'),
        'shortname' => 'wordcloud',
        'requiredcapability' => 'mod/wordcloud:submit',
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);

$functions = array(
    'mod_wordcloud_add_word' => array(
        'classname' => 'mod_wordcloud_external',
        'methodname' => 'add_word',
        'classpath' => 'mod/wordcloud/externallib.php',
        'description' => 'Update question progress of a student',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true
    ),
    'mod_wordcloud_get_words' => array(
        'classname' => 'mod_wordcloud_external',
        'methodname' => 'get_words',
        'classpath' => 'mod/wordcloud/externallib.php',
        'description' => 'Get the latest word cloud html',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true
    )
);