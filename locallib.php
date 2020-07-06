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

defined('MOODLE_INTERNAL') || die;

/**
 * creates the wordcloud html
 *
 * @param int $wordcloudid
 * @return string
 * @throws dml_exception
 */
function mod_wordcloud_get_cloudhtml($wordcloudid) {
    global $DB;

    $sql = 'SELECT min(count) as mincount, max(count) as maxcount
          FROM {wordcloud_map}
         WHERE wordcloudid = :wordcloudid';
    $wordcnt = $DB->get_record_sql($sql, ['wordcloudid' => $wordcloudid]);

    $records = $DB->get_records('wordcloud_map', ['wordcloudid' => $wordcloudid]);
    $cloudhtml = '';

    $range = max(.01, $wordcnt->maxcount - $wordcnt->mincount) * 1.0001;
    if ($range >= 6) {
        $steps = 6;
    } else {
        $steps = 1;
    }

    foreach ($records as $row) {
        $weight = 1 + floor($steps * ($row->count - $wordcnt->mincount) / $range);
        $fontsize = 'mod-wordcloud-w' . $weight;
        $cloudhtml .= '<span class="mod_wordcloud_word mod-wordcloud-center ' . $fontsize . '"
                title="' . $row->count . '">' . $row->word . '</span>';
    }
    return $cloudhtml;
}