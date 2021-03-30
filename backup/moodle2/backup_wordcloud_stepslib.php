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
 * Class for the structure used to backup one wordcloud activity.
 *
 * @package   mod_wordcloud
 * @copyright 2020 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete wordcloud structure for backup, with file and id annotations
 *
 * @package   mod_wordcloud
 * @category  backup
 * @copyright 2020 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_wordcloud_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        $userinfo = $this->get_setting_value('userinfo');

        $wordcloud = new backup_nested_element('wordcloud', array('id'),
                array('course', 'name', 'intro', 'introformat', 'timecreated', 'timemodified'));

        $wordcloud->set_source_table('wordcloud', array('id' => backup::VAR_ACTIVITYID));

        if ($userinfo) {
            $wordcloudmap = new backup_nested_element('wordcloudmap', array('id'), array('wordcloudid', 'word', 'count'));

            $wordcloud->add_child($wordcloudmap);
            $wordcloudmap->set_source_table('wordcloud_map', array('wordcloudid' => backup::VAR_PARENTID));
        }

        $wordcloud->annotate_files('mod_wordcloud', 'intro', null);
        return $this->prepare_activity_structure($wordcloud);
    }
}
