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
 * Private page module utility functions
 *
 * @package mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('WORDCLOUD_WORD_LENGTH', 40);
define('WORDCLOUD_MAX_WORDS', 300);
define('WORDCLOUD_MAX_TIME', 2147483647);

use core_privacy\local\request\transform;

/**
 * creates the wordcloud html
 *
 * @param int $wordcloudid
 * @param int $groupmode
 * @param int $groupid
 * @param int $listview
 * @param bool $canedit
 * @return array
 * @throws dml_exception
 */
function mod_wordcloud_get_cloudhtml($wordcloudid, $groupmode = 0, $groupid = 0, $listview = 0, $canedit = false) {
    global $DB, $PAGE, $USER;

    if ($groupmode && $groupid === 0) {
        $sql = 'SELECT word, sum(count) AS count
                  FROM {wordcloud_map}
                 WHERE wordcloudid = :wordcloudid
                   AND groupid != 0
              GROUP BY word';
        $records = $DB->get_records_sql($sql, ['wordcloudid' => $wordcloudid]);

        $sql = 'SELECT min(count) AS mincount, max(count) AS maxcount
              FROM (SELECT word, sum(count) AS count
                      FROM {wordcloud_map}
                     WHERE wordcloudid = :wordcloudid
                       AND groupid != 0
                  GROUP BY word) AS subq';
        $wordcnt = $DB->get_record_sql($sql, ['wordcloudid' => $wordcloudid]);
        $sumcount = $DB->get_record_sql('SELECT sum(count) AS count FROM {wordcloud_map} WHERE wordcloudid = :wordcloudid AND groupid != 0',
            ['wordcloudid' => $wordcloudid]);
    } else {
        $sql = 'SELECT min(count) AS mincount, max(count) AS maxcount
                  FROM {wordcloud_map}
                 WHERE wordcloudid = :wordcloudid
                   AND groupid = :groupid';
        $wordcnt = $DB->get_record_sql($sql, ['wordcloudid' => $wordcloudid, 'groupid' => $groupid]);

        $records = $DB->get_records('wordcloud_map', ['wordcloudid' => $wordcloudid, 'groupid' => $groupid], 'id');
        $sumcount = $DB->get_record_sql('SELECT sum(count) AS count FROM {wordcloud_map} WHERE wordcloudid = :wordcloudid AND groupid = :groupid',
            ['wordcloudid' => $wordcloudid, 'groupid' => $groupid]);
    }

    if (!$sumcount->count) {
        $sumcount->count = 0;
    }

    $cloudhtml = '';
    if ($listview) {
        $renderer = $PAGE->get_renderer('core');
        $cloudhtml = $renderer->render_from_template('mod_wordcloud/wordlist', ['words' => array_values($records)]);
    } else {
        // The range is slightly larger than max-min count to make sure that the largest element is rounded down.
        $range = ($wordcnt->maxcount - $wordcnt->mincount) * 1.0001;
        $steps = 6;

        foreach ($records as $row) {
            if ($range >= 3) {
                $weight = 1 + floor($steps * ($row->count - $wordcnt->mincount) / $range);
            } else {
                $weight = 1;
            }
            $fontsize = 'w' . $weight;
            $cloudhtml .= '<span class="word center ' . $fontsize . '"
                title="' . $row->count . '">' . $row->word . '</span>';
        }
    }

    if (!$canedit) {
        $visibility = $DB->get_record('wordcloud', ['id' => $wordcloudid])->visibility;

        switch($visibility) {
            case 1:
                $sql = 'SELECT 1
                        FROM {wordcloud_map} m
                        JOIN {wordcloud_word_user_rel} r
                        ON m.id = r.mapid
                        WHERE wordcloudid = :wordcloudid
                          AND groupid = :groupid
                          AND userid = :userid';
                if (!$DB->record_exists_sql($sql, ['wordcloudid' => $wordcloudid, 'groupid' => $groupid, 'userid' => $USER->id])) {
                    $cloudhtml = get_string('userinfosubmit', 'wordcloud');
                    $sumcount->count = 0;
                }
                break;
            case 2:
                $record = $DB->get_record('wordcloud', ['id' => $wordcloudid]);
                $time = time();
                if ($record->timeclose > $time) {
                    $cloudhtml = get_string('userinfotime', 'wordcloud');
                    $sumcount->count = 0;
                }
                break;
            default:
                break;
        }
    }

    return ['cloudhtml' => $cloudhtml, 'sumcount' => $sumcount->count];
}

/**
 * Check if user is allowed to submit a word
 *
 * @param object $wordcloud
 * @param object $context
 * @param int $groupid
 * @return array
 * @throws coding_exception
 */
function mod_wordcloud_can_submit($wordcloud, $context, $groupid = 0) {
    $time = time();
    $timeclose = $wordcloud->timeclose ? : WORDCLOUD_MAX_TIME;

    $result = [
        'timeopen' => $wordcloud->timeopen ? transform::datetime($wordcloud->timeopen) : null,
        'timeclose' => $wordcloud->timeclose ? transform::datetime($wordcloud->timeclose) : null,
        'timing' => null,
    ];

    if ($wordcloud->timeopen || $wordcloud->timeclose) {
        $result['timing'] = 1;
    }

    $result['writeaccess'] = false;

    if (has_capability('mod/wordcloud:submit', $context) && ($time >= $wordcloud->timeopen && $time <= $timeclose)) {
        $cm = get_coursemodule_from_instance('wordcloud', $wordcloud->id, 0, false, MUST_EXIST);
        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode) {
            if ($groupid !== 0 && (has_capability('mod/wordcloud:editentry', $context) || groups_is_member($groupid))) {
                $result['writeaccess'] = true;
            }
        } else {
            $result['writeaccess'] = true;
        }
    }

    return $result;
}
