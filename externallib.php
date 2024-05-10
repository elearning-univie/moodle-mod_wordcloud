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
        return new external_function_parameters([
                'aid' => new external_value(PARAM_INT, 'id of the wordcloud activity'),
                'word' => new external_value(PARAM_TEXT, 'word to be added'),
                'groupid' => new external_value(PARAM_INT, 'id of the wordcloud activity', VALUE_DEFAULT, 0),
                'listview' => new external_value(PARAM_INT, 'display cloud as list'),
            ]
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_words_parameters() {
        return new external_function_parameters([
                'aid' => new external_value(PARAM_INT, 'id of the wordcloud activity'),
                'timestamphtml' => new external_value(PARAM_INT, 'timestamp of the last wordcloud change'),
                'listview' => new external_value(PARAM_INT, 'display cloud as list'),
            ]
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function update_entry_parameters() {
        return new external_function_parameters([
                'aid' => new external_value(PARAM_INT, 'id of the wordcloud activity'),
                'entry' => new external_multiple_structure(
                    new external_single_structure([
                            'wordid' => new external_value(PARAM_TEXT, 'id of the word to change'),
                            'newword' => new external_value(PARAM_TEXT, 'new word to change to'),
                            'newcount' => new external_value(PARAM_INT, 'new word count to change to'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Add a new word or increase the count of the word
     *
     * @param int $aid
     * @param string $word
     * @param int $groupid
     * @param int $listview
     * @return array|null
     */
    public static function add_word($aid, $word, $groupid, $listview) {
        global $DB, $USER;

        $warnings = [];

        $params = self::validate_parameters(self::add_word_parameters(),
            ['aid' => $aid, 'word' => $word, 'groupid' => $groupid, 'listview' => $listview]);
        $cm = get_coursemodule_from_instance('wordcloud', $params['aid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);

        self::validate_context($context);
        require_login($course, false, $cm);
        require_capability('mod/wordcloud:submit', $context);

        $canedit = has_capability('mod/wordcloud:editentry', $context);

        $wordcloud = $DB->get_record('wordcloud', ['id' => $params['aid']]);
        $groupmode = groups_get_activity_groupmode($cm);
        $servergroupid = 0;

        if ($groupmode) {
            $servergroupid = $params['groupid'] ?: groups_get_activity_group($cm);
        }

        $cansubmit = mod_wordcloud_can_submit($wordcloud, $context, $servergroupid);

        if (!$cansubmit['writeaccess']) {
            return ['cloudhtml' => '', 'sumcount' => 0, 'warnings' => $warnings];
        }

        $params['word'] = trim($params['word']);

        if (mb_strlen($params['word'], 'UTF-8') > WORDCLOUD_WORD_LENGTH) {
            $warnings[] = [
                    'warningcode' => 'errorwordoverflow',
                    'message' => get_string('errorwordoverflow', 'mod_wordcloud'),
            ];
            return ['cloudhtml' => '', 'sumcount' => 0, 'warnings' => $warnings];
        } else if (strlen($params['word']) == 0) {
            return ['cloudhtml' => '', 'sumcount' => 0, 'warnings' => $warnings];
        }

        $record = $DB->get_record('wordcloud_map', ['wordcloudid' => $params['aid'], 'groupid' => $servergroupid,
            'word' => $params['word']]);

        if (!$record) {
            $wordscount = $DB->count_records('wordcloud_map', ['wordcloudid' => $params['aid'], 'groupid' => $servergroupid]);

            if ($wordscount > WORDCLOUD_MAX_WORDS) {
                $warnings[] = [
                        'warningcode' => 'errortoomanywords',
                        'message' => get_string('errortoomanywords', 'mod_wordcloud'),
                ];
                return ['cloudhtml' => '', 'sumcount' => 0, 'warnings' => $warnings];
            }

            $mapid = $DB->insert_record('wordcloud_map', ['wordcloudid' => $params['aid'], 'groupid' => $servergroupid,
                'word' => $params['word'], 'count' => 1]);
        } else {
            $record->count++;
            $mapid = $record->id;
            $DB->update_record('wordcloud_map', $record);
        }

        if (!$DB->record_exists('wordcloud_word_user_rel', ['mapid' => $mapid, 'userid' => $USER->id])) {
            $DB->insert_record('wordcloud_word_user_rel', ['mapid' => $mapid, 'userid' => $USER->id]);

            // Update completion state
            $completion = new completion_info($course);
            if ($completion->is_enabled($cm) && $wordcloud->completionsubmits) {
                $sql = 'SELECT count(*)
                FROM {wordcloud_map} m
                JOIN {wordcloud_word_user_rel} r
                ON m.id = r.mapid
                AND r.userid = :userid
                AND m.wordcloudid = :wordcloudid';

                $submitcount = $DB->count_records_sql($sql, ['userid' => $USER->id, 'wordcloudid' => $params['aid']]);

                if ($submitcount >= $wordcloud->completionsubmits) {
                    $completion->update_state($cm, COMPLETION_COMPLETE);
                }
            }
        }

        $DB->set_field('wordcloud', 'lastwordchange', time(), ['id' => $params['aid']]);
        $cloudhtml = mod_wordcloud_get_cloudhtml($params['aid'], $groupmode, $servergroupid, $params['listview'], $canedit);
        return ['cloudhtml' => $cloudhtml['cloudhtml'],
            'sumcount' => $cloudhtml['sumcount'],
            'warnings' => $warnings];
    }

    /**
     * Get the latest wordcloud html
     *
     * @param int $aid
     * @param int $timestamphtml
     * @param int $listview
     * @return array|null
     */
    public static function get_words($aid, $timestamphtml, $listview) {
        global $DB;

        $warnings = [];

        $params = self::validate_parameters(self::get_words_parameters(), ['aid' => $aid, 'timestamphtml' => $timestamphtml, 'listview' => $listview]);
        $cm = get_coursemodule_from_instance('wordcloud', $params['aid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);
        $groupid = 0;

        self::validate_context($context);
        require_login($course, false, $cm);
        require_capability('mod/wordcloud:view', $context);

        $canedit = has_capability('mod/wordcloud:editentry', $context);

        if ($groupmode = groups_get_activity_groupmode($cm)) {
            $groupid = groups_get_activity_group($cm, true);
            if ($groupmode != VISIBLEGROUPS &&
                !has_capability('moodle/site:accessallgroups', $context) &&
                !groups_is_member($groupid)) {
                return ['cloudhtml' => '', 'sumcount' => 0, 'timestamphtml' => 0, 'warnings' => $warnings];
            }
        }

        $record = $DB->get_record('wordcloud', ['id' => $params['aid']]);

        if ($record->lastwordchange > $timestamphtml) {
            $cloudhtml = mod_wordcloud_get_cloudhtml($params['aid'], $groupmode, $groupid, $params['listview'], $canedit);
            return ['cloudhtml' => $cloudhtml['cloudhtml'],
                'sumcount' => $cloudhtml['sumcount'],
                'timestamphtml' => $record->lastwordchange,
                'warnings' => $warnings];
        }

        return ['cloudhtml' => '', 'sumcount' => 0, 'timestamphtml' => 0, 'warnings' => $warnings];
    }

    /**
     * Add a new word or increase the count of the word
     *
     * @param int $aid
     * @param object $entry
     * @return array
     */
    public static function update_entry($aid, $entry) {
        global $DB;

        $warnings = [];
        $errentries = '';
        $success = true;

        $params = self::validate_parameters(self::update_entry_parameters(),
            ['aid' => $aid, 'entry' => $entry]);
        $cm = get_coursemodule_from_instance('wordcloud', $params['aid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);

        self::validate_context($context);
        require_login($course, false, $cm);
        require_capability('mod/wordcloud:editentry', $context);

        $groupmode = groups_get_activity_groupmode($cm);
        $groupid = $groupmode ? groups_get_activity_group($cm) : 0;

        if ($groupmode && $groupid === 0) {
            return ['success' => false, 'warnings' => $warnings];
        }

        foreach ($params['entry'] as $updateentry) {
            $updateentry['newword'] = trim($updateentry['newword']);

            if (mb_strlen($updateentry['newword'], 'UTF-8') > WORDCLOUD_WORD_LENGTH) {
                $warnings[] = [
                    'warningcode' => 'errorwordoverflow',
                    'message' => get_string('errorwordoverflow', 'mod_wordcloud'),
                ];
                $success = false;
                continue;
            } else if (strlen($updateentry['newword']) == 0) {
                $success = false;
                continue;
            }

            $record = $DB->get_record('wordcloud_map', ['id' => $updateentry['wordid'], 'groupid' => $groupid]);

            if ($record) {
                $checkrec = $DB->get_record('wordcloud_map',
                    ['word' => $updateentry['newword'], 'wordcloudid' => $params['aid'], 'groupid' => $groupid]);
                if ($checkrec) {
                    if ($checkrec->id != $updateentry['wordid']) {
                        $DB->delete_records('wordcloud_map', ['id' => $checkrec->id]);
                        $DB->delete_records('wordcloud_word_user_rel', ['mapid' => $checkrec->id]);
                        $updateentry['newcount'] = $updateentry['newcount'] + $checkrec->count;
                    }
                }
                $record->word = $updateentry['newword'];
                $record->count = $updateentry['newcount'];
                $DB->update_record('wordcloud_map', $record);
            }
        }

        if (!$success) {
            $warnings[] = [
                'warningcode' => 'errorwordoverflow',
                'message' => get_string('errorupdateentries', 'mod_wordcloud') . $errentries,
            ];
        }

        return ['success' => $success, 'warnings' => $warnings];
    }

    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function add_word_returns() {
        return new external_single_structure([
                'cloudhtml' => new external_value(PARAM_RAW, 'wordcloud html code'),
                'sumcount' => new external_value(PARAM_INT, 'total number of words submitted'),
                'warnings' => new external_warnings(),
        ]);
    }

    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function get_words_returns() {
        return new external_single_structure([
                'cloudhtml' => new external_value(PARAM_RAW, 'wordcloud html code'),
                'sumcount' => new external_value(PARAM_INT, 'total number of words submitted'),
                'timestamphtml' => new external_value(PARAM_INT, 'timestamp of the last wordcloud change'),
                'warnings' => new external_warnings(),
        ]);
    }

    /**
     * Returns return value description
     *
     * @return external_value
     */
    public static function update_entry_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'true if successful, false otherwise'),
            'warnings' => new external_warnings(),
        ]);
    }
}
