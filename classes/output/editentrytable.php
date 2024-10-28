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
use moodle_url;
use html_writer;

/**
 * Table class for displaying the wordcloud edit word entries list.
 *
 * @copyright  2021 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editentrytable extends table_sql {
    /** @var string text for the edit icon */
    private $editicontext;

    /** @var string text for the delete icon */
    private $deleteicontext;

    /** @var int course module id */
    private $cmid;

    /**
     * editentrytable constructor.
     *
     * @param string $uniqueid
     * @param int $cmid
     * @throws \coding_exception
     */
    public function __construct($uniqueid, $cmid) {
        parent::__construct($uniqueid);
        $this->cmid = $cmid;

        $this->editicontext = get_string('save', 'moodle');
        $this->deleteicontext = get_string('remove', 'moodle');

        // Define the list of columns to show.
        $columns = ['word', 'count', 'response', 'remove'];
        $this->define_columns($columns);
        $this->column_class('word', 'word');
        $this->column_class('count', 'count');
        $this->column_class('remove', 'remove');

        // Define the titles of columns to show in header.
        $headers = [
            get_string('word', 'mod_wordcloud'),
            get_string('count', 'mod_wordcloud'),
            '',
            get_string('remove'),
            ];
        $this->define_headers($headers);

        $this->collapsible(false);
        $this->pageable(true);
        $this->is_downloadable(false);

        $this->no_sorting('response');
        $this->no_sorting('remove');
    }

    /**
     * Prepares column edit for display
     *
     * @param object $values
     * @return string
     */
    public function col_word($values) {
        return html_writer::tag('input', null, ['id' => 'mod-wordcloud-word' . $values->id,
            'type' => 'text', 'class' => 'form-control mod-wordcloud-edit-word', 'maxlength' => '40', 'value' => $values->word,
            'data-word' => $values->word, 'data-id' => $values->id]);
    }

    /**
     * Prepares column edit for display
     *
     * @param object $values
     * @return string
     */
    public function col_count($values) {
        return html_writer::tag('input', null, ['id' => 'mod-wordcloud-count' . $values->id,
            'type' => 'text', 'class' => 'form-control mod-wordcloud-edit-count', 'maxlength' => '10', 'value' => $values->count,
            'data-value' => $values->count, 'data-id' => $values->id]);
    }

    /**
     * Prepares column response for display
     *
     * @param object $values
     * @return string
     */
    public function col_response($values) {
        global $OUTPUT;

        return html_writer::div($OUTPUT->pix_icon('t/check', ''), 'success', ['id' => 'mod-wordcloud-fade-success' . $values->id]);
    }

    /**
     * Prepares column delete for display
     *
     * @param object $values
     * @return string
     */
    public function col_remove($values) {
        global $OUTPUT;

        $durl = new moodle_url('/mod/wordcloud/editentry.php',
            ['id' => $this->cmid, 'deleteselected' => $values->id, 'sesskey' => sesskey()]);

        return html_writer::link($durl, $OUTPUT->pix_icon('t/delete', $this->deleteicontext));
    }
}
