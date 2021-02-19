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
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_words_parameters() {
        return new external_function_parameters(
                array(
                        'aid' => new external_value(PARAM_INT, 'id of the wordcloud activity'),
                        'timestamphtml' => new external_value(PARAM_INT, 'timestamp of the last wordcloud change')
                )
        );
    }

    /**
     * Add a new word or increase the count of the word
     *
     * @param int $aid
     * @param string $word
     * @return array|null
     */
    public static function add_word($aid, $word) {
        global $DB;

        $warnings = [];

        $params = self::validate_parameters(self::add_word_parameters(), array('aid' => $aid, 'word' => $word));
        $cm = get_coursemodule_from_instance('wordcloud', $params['aid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);

        self::validate_context($context);
        require_login($course, false, $cm);
        require_capability('mod/wordcloud:submit', $context);

        $params['word'] = trim($params['word']);

        if (mb_strlen($params['word'], 'UTF-8') > WORDCLOUD_WORD_LENGTH) {
            $warnings[] = [
                    'warningcode' => 'errorwordoverflow',
                    'message' => get_string('errorwordoverflow', 'mod_wordcloud')
            ];
            return ['cloudhtml' => '', 'warnings' => $warnings];
        } else if (strlen($params['word']) == 0) {
            return ['cloudhtml' => '', 'warnings' => $warnings];
        }

        $record = $DB->get_record('wordcloud_map', ['wordcloudid' => $params['aid'], 'word' => $params['word']]);

        if (!$record) {
            $wordscount = $DB->count_records('wordcloud_map', ['wordcloudid' => $params['aid']]);

            if ($wordscount > WORDCLOUD_MAX_WORDS) {
                $warnings[] = [
                        'warningcode' => 'errortoomanywords',
                        'message' => get_string('errortoomanywords', 'mod_wordcloud')
                ];
                return ['cloudhtml' => '', 'warnings' => $warnings];
            }

            $DB->insert_record('wordcloud_map', ['wordcloudid' => $params['aid'], 'word' => $params['word'], 'count' => 1]);
        } else {
            $record->count++;
            $DB->update_record('wordcloud_map', $record);
        }

        $DB->set_field('wordcloud', 'lastwordchange', time(), ['id' => $params['aid']]);
        return ['cloudhtml' => mod_wordcloud_get_cloudhtml($params['aid']), 'warnings' => $warnings];
    }

    /**
     * Get the latest wordcloud html
     *
     * @param int $aid
     * @param int $timestamphtml
     * @return array|null
     */
    public static function get_words($aid, $timestamphtml) {
        global $DB;

        $warnings = [];

        $params = self::validate_parameters(self::get_words_parameters(), array('aid' => $aid, 'timestamphtml' => $timestamphtml));
        $cm = get_coursemodule_from_instance('wordcloud', $params['aid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);

        self::validate_context($context);
        require_login($course, false, $cm);
        require_capability('mod/wordcloud:view', $context);

        $record = $DB->get_record('wordcloud', ['id' => $params['aid']]);

        if ($record->lastwordchange > $timestamphtml) {
            return ['cloudhtml' => mod_wordcloud_get_cloudhtml($params['aid']), 'timestamphtml' => $record->lastwordchange,
                    'warnings' => $warnings];
        }

        return ['cloudhtml' => '', 'timestamphtml' => 0, 'warnings' => $warnings];
    }

    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function add_word_returns() {
        return new external_single_structure(array(
                'cloudhtml' => new external_value(PARAM_RAW, 'wordcloud html code'),
                'warnings' => new external_warnings()
        ));
    }

    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function get_words_returns() {
        return new external_single_structure(array(
                'cloudhtml' => new external_value(PARAM_RAW, 'wordcloud html code'),
                'timestamphtml' => new external_value(PARAM_INT, 'timestamp of the last wordcloud change'),
                'warnings' => new external_warnings()
        ));
    }
}
