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
            get_string('word',  'mod_wordcloud'),
            get_string('count', 'mod_wordcloud')
        ]);

        $this->collapsible(false);
        $this->pageable(false);
        $this->is_downloadable(false);
        $this->sortable(true, 'count', SORT_DESC);
    }
}