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
 * Interface implementation of the external Webservices
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once(__DIR__ . '/locallib.php');

/**
 * Class mod_wordcloud_external
 *
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_wordcloud_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function add_word_parameters() {
        return new external_function_parameters(
            array(
                'aid' => new external_value(PARAM_INT, 'id of the wordcloud activity'),
                'word' => new external_value(PARAM_TEXT, 'word to be added')
            )
        );
    }

    /**
     * Add a new word or increase the count of the word
     *
     * @param int $aid
     * @param string $word
     * @return string|null
     */
    public static function add_word($aid, $word) {
        global $DB;

        $params = self::validate_parameters(self::add_word_parameters(), array('aid' => $aid, 'word' => $word));
        if (strlen($params['word']) > 40) {
            return "word too long";
        }

        $record = $DB->get_record('wordcloud_map', ['wordcloudid' => $params['aid'], 'word' => $params['word']]);

        if (!$record) {
            $wordscount = $DB->count_records('wordcloud_map', ['wordcloudid' => $params['aid']]);

            if ($wordscount > 128) {
                return "too many words";
            }

            $DB->insert_record('wordcloud_map', ['wordcloudid' => $params['aid'], 'word' => $params['word'], 'count' => 1]);
        } else {
            $record->count++;
            $DB->update_record('wordcloud_map', $record);
        }

        return mod_wordcloud_get_cloudhtml($params['aid']);
    }

    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function add_word_returns() {
        return new external_value(PARAM_RAW, 'wordcloud html code');
    }
}
