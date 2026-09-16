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
 * Behat data generator for mod_wordcloud.
 *
 * @package   mod_wordcloud
 * @category  test
 * @copyright 2022 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_wordcloud_generator extends behat_generator_base {
    /**
     * Get a list of the entities that Behat can create using the generator step.
     *
     * Lets feature files seed submitted words directly, for example:
     *
     *     And the following "mod_wordcloud > entries" exist:
     *       | wordcloud      | word       | count | user     |
     *       | Test wordcloud | test word0 | 3     | student1 |
     *
     * @return array
     */
    protected function get_creatable_entities(): array {
        return [
            'entries' => [
                'singular' => 'entry',
                'datagenerator' => 'entry',
                'required' => ['wordcloud', 'word'],
                'switchids' => [
                    'wordcloud' => 'wordcloudid',
                    'user' => 'userid',
                    'group' => 'groupid',
                ],
            ],
        ];
    }

    /**
     * Get the wordcloud instance id from an activity idnumber or name.
     *
     * @param string $idnumberorname The wordcloud activity idnumber or name.
     * @return int The wordcloud instance id.
     */
    protected function get_wordcloud_id(string $idnumberorname): int {
        return $this->get_cm_by_activity_name('wordcloud', $idnumberorname)->instance;
    }
}
