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
define('WORDCLOUD_WORD_LENGTH', 40);
define('WORDCLOUD_MAX_WORDS', 128);

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

    $records = $DB->get_records('wordcloud_map', ['wordcloudid' => $wordcloudid], 'id');
    $cloudhtml = '';

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
    return $cloudhtml;
}

/**
 * Download the wordcloud as csv file
 *
 * @param int $wordcloudid
 * @throws coding_exception
 * @throws dml_exception
 */
function mod_wordcloud_download_csv($wordcloudid) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');

    $records = $DB->get_records('wordcloud_map', ['wordcloudid' => $wordcloudid]);

    $csvexport = new csv_export_writer('semicolon');
    $filename = get_string('pluginname', 'mod_wordcloud');
    $filename .= clean_filename('-' . gmdate("Ymd_Hi")) . '.csv';
    $csvexport->filename = $filename;
    $csvexport->add_data([get_string('word', 'mod_wordcloud'), get_string('count', 'mod_wordcloud')]);

    foreach ($records as $record) {
        $word = str_replace(';', ',', $record->word);
        $csvexport->add_data([$word, $record->count]);
    }
    $csvexport->download_file();
}