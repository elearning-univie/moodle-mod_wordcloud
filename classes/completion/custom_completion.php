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

declare(strict_types=1);

namespace mod_wordcloud\completion;

use core_completion\activity_custom_completion;

/**
 * Activity custom completion subclass for the wordcloud activity.
 *
 * @package mod_wordcloud
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {
    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $userid = $this->userid;
        $wordcloudid = $this->cm->instance;

        if (!$wordcloud = $DB->get_record('wordcloud', ['id' => $wordcloudid])) {
            throw new \moodle_exception('Unable to find wordcloud with id ' . $wordcloudid);
        }

        $postcountparams = ['userid' => $userid, 'wordcloudid' => $wordcloudid];
        $postcountsql = "SELECT COUNT(*)
                           FROM {wordcloud_word_user_rel} wur
                           JOIN {wordcloud_map} m ON wur.mapid = m.id
                          WHERE wur.userid = :userid
                            AND m.wordcloudid = :wordcloudid";

        if ($rule == 'completionsubmits') {
            $status = $wordcloud->completionsubmits <= $DB->get_field_sql($postcountsql, $postcountparams);
        }

        return $status ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [
            'completionsubmits',
        ];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        $completionsubmits = $this->cm->customdata['customcompletionrules']['completionsubmits'] ?? 0;

        return [
            'completionsubmits' => get_string('completiondetail:submits', 'wordcloud', $completionsubmits),
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionsubmits',
        ];
    }
}
