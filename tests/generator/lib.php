<?php
// This file is part of mod_publication for Moodle - http://moodle.org/
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
 * Generator file for mod_wordcloud
 *
 * @package   mod_wordcloud
 * @category  test
 * @copyright 2022 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * wordcloud module data generator class
 *
 * @package   mod_wordcloud
 * @category  test
 * @copyright 2022 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_wordcloud_generator extends testing_module_generator {

    /**
     * Generator method creating a mod_wordcloud instance.
     *
     * @param array|stdClass $record (optional) Named array containing instance settings
     * @param array $options (optional) general options for course module. Can be merged into $record
     * @return stdClass record from module-defined table
     */
    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;

        if (!isset($record->type)) {
            $record->type = 'general';
        }
        if (!isset($record->assessed)) {
            $record->assessed = 0;
        }
        if (!isset($record->scale)) {
            $record->scale = 0;
        }

        return parent::create_instance($record, (array)$options);
    }
}
