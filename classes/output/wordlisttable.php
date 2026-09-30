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
 * Table class for editing all entries in a wordcloud activity.
 *
 * @package    mod_wordcloud
 * @copyright  2021 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_wordcloud\output;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

use table_sql;

/**
 * Class wordlisttable
 *
 * A table_sql implementation to list words and their counts in a word cloud instance.
 */
class wordlisttable extends table_sql {
    /**
     * Constructor.
     *
     * @param string $uniqueid  A unique identifier for this table instance.
     * @param int    $cmid      The course module ID for this wordcloud instance.
     */
    public function __construct($uniqueid, $cmid) {
        parent::__construct($uniqueid);

        $this->define_columns(['word', 'count']);
        $this->define_headers([
            get_string('word', 'mod_wordcloud'),
            get_string('count', 'mod_wordcloud'),
        ]);

        $this->set_attribute('id', 'mod-wordcloud-wordlist');
        $this->column_class('count', 'w-25');
        $this->collapsible(false);
        $this->pageable(true);
        $this->is_downloadable(false);
        $this->sortable(true, 'count', SORT_DESC);
    }

    /**
     * Lists the words of a wordcloud; in group mode, "all participants" (group 0) sums them over all groups.
     *
     * @param int $wordcloudid the wordcloud instance id.
     * @param int $groupmode the activity's group mode.
     * @param int $groupid the selected group.
     */
    public function set_words_source(int $wordcloudid, int $groupmode, int $groupid): void {
        if ($groupmode && $groupid === 0) {
            $this->set_sql(
                '*',
                '(SELECT MIN(id) AS id, word, SUM(count) AS count
                    FROM {wordcloud_map}
                   WHERE wordcloudid = :wordcloudid AND groupid <> 0 AND count > 0
                GROUP BY word) words',
                '1 = 1',
                ['wordcloudid' => $wordcloudid]
            );
        } else {
            $this->set_sql(
                '*',
                '{wordcloud_map}',
                'wordcloudid = :wordcloudid AND groupid = :groupid AND count > 0',
                ['wordcloudid' => $wordcloudid, 'groupid' => $groupid]
            );
        }
    }

    /**
     * Words are stored as entered, so they must be escaped.
     *
     * @param stdClass $row the table row.
     * @return string
     */
    public function col_word($row) {
        return s($row->word);
    }
}
