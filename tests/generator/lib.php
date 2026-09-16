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
     * @param ?array $options (optional) general options for course module. Can be merged into $record
     * @return stdClass record from module-defined table
     */
    public function create_instance($record = null, ?array $options = null) {
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

    /**
     * Creates a submitted word entry in a wordcloud activity.
     *
     * Lets tests seed a wordcloud with words directly instead of driving the UI
     * once per submission, which keeps feature files focused on the behaviour
     * they actually assert.
     *
     * If the word already exists for the given activity and group its count is
     * increased, mirroring what happens when a word is submitted repeatedly.
     *
     * @param array|stdClass $record Fields: wordcloudid (required), word (required),
     *                               count (default 1), groupid (default 0), userid (optional).
     * @return stdClass the wordcloud_map record.
     */
    public function create_entry($record) {
        global $DB;

        $record = (array) $record;

        if (empty($record['wordcloudid'])) {
            throw new coding_exception('The wordcloudid value is required when creating a wordcloud entry.');
        }

        if (!isset($record['word']) || trim($record['word']) === '') {
            throw new coding_exception('The word value is required when creating a wordcloud entry.');
        }

        $wordcloudid = $record['wordcloudid'];
        $word = trim($record['word']);
        $groupid = $record['groupid'] ?? 0;
        $count = isset($record['count']) ? (int) $record['count'] : 1;

        $existing = $DB->get_record('wordcloud_map', [
            'wordcloudid' => $wordcloudid,
            'groupid' => $groupid,
            'word' => $word,
        ]);

        if ($existing) {
            $existing->count += $count;
            $DB->update_record('wordcloud_map', $existing);
            $mapid = $existing->id;
        } else {
            $mapid = $DB->insert_record('wordcloud_map', [
                'wordcloudid' => $wordcloudid,
                'groupid' => $groupid,
                'word' => $word,
                'count' => $count,
            ]);
        }

        // Record who submitted the word, so that completion, the visibility
        // rules and the privacy provider all behave as they would in real use.
        if (!empty($record['userid'])) {
            $rel = ['mapid' => $mapid, 'userid' => $record['userid']];
            if (!$DB->record_exists('wordcloud_word_user_rel', $rel)) {
                $DB->insert_record('wordcloud_word_user_rel', $rel);
            }
        }

        $DB->set_field('wordcloud', 'lastwordchange', time(), ['id' => $wordcloudid]);

        return $DB->get_record('wordcloud_map', ['id' => $mapid]);
    }
}
