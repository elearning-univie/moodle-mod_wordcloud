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

namespace mod_wordcloud\courseformat;

use core\output\action_link;
use core\output\local\properties\button;
use core\output\local\properties\text_align;
use core\output\renderer_helper;
use core\url;
use core_courseformat\local\overview\overviewitem;
use cm_info;

/**
 * Overview class for the wordcloud activity.
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class overview extends \core_courseformat\activityoverviewbase {
    /** @var \stdClass|false $wordcloud the wordcloud instance record. */
    private $wordcloud;

    /**
     * Constructor.
     *
     * @param cm_info $cm the course module instance.
     * @param \moodle_database $db the database access.
     * @param \core\clock $clock the clock interface.
     */
    public function __construct(
        // The activity course module.
        cm_info $cm,
        /** @var \moodle_database $db the database acces. */
        protected readonly \moodle_database $db,
        /** @var \core\clock $clock the clock interface */
        protected readonly \core\clock $clock
    ) {
        parent::__construct($cm);
        $this->wordcloud = $this->db->get_record('wordcloud', ['id' => $this->cm->instance]);
    }

    #[\Override]
    public function get_due_date_overview(): ?overviewitem {
        global $DB;

        if (empty($this->wordcloud->timeclose)) {
            return new overviewitem(
                name: get_string('activityclose', 'mod_wordcloud'),
                value: null,
                content: '-',
            );
        }

        return new overviewitem(
            name: get_string('activityclose', 'mod_wordcloud'),
            value: $this->wordcloud->timeclose,
            content: userdate($this->wordcloud->timeclose)
        );
    }

    #[\Override]
    public function get_extra_overview_items(): array {
        return [
            'wordcount' => $this->get_extra_wordcount(),
            'action' => $this->get_extra_action(),
        ];
    }

    /**
     * Build the overview item showing the number of submitted words.
     *
     * @return overviewitem|null the overview item.
     */
    private function get_extra_wordcount(): ?overviewitem {
        $filter = 'wordcloudid = :wordcloudid AND count > 0';
        $params = ['wordcloudid' => $this->wordcloud->id];

        $sumcount = $this->db->get_record_sql('SELECT sum(count) AS count FROM {wordcloud_map} WHERE ' . $filter, $params);

        if (!$sumcount->count) {
            $sumcount->count = 0;
        }

        return new overviewitem(
            name: get_string('submittedwords', 'mod_wordcloud'),
            value: $sumcount->count,
            content: $sumcount->count,
            textalign: text_align::END,
        );
    }

    /**
     * Build the overview item with the action link.
     *
     * @return overviewitem|null the overview item.
     */
    private function get_extra_action(): ?overviewitem {
        if (!has_capability('mod/wordcloud:editentry', $this->cm->context)) {
            return null;
        }

        $content = new action_link(
            url: new url(
                '/mod/wordcloud/view.php',
                ['id' => $this->cm->id],
            ),
            text: get_string('view'),
            attributes: ['class' => button::SECONDARY_OUTLINE->classes()],
        );
        return new overviewitem(
            name: get_string('actions'),
            value: '',
            content: $content,
            textalign: text_align::CENTER,
        );
    }
}
