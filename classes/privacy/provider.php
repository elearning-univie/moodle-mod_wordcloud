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
 * Privacy Subsystem implementation for mod_wordcloud.
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_wordcloud\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for mod_wordcloud.
 *
 * The plugin itself stores which word a user submitted (via wordcloud_word_user_rel,
 * linking a user to a wordcloud_map entry). The word text and its aggregated count in
 * wordcloud_map are shared/collective data belonging to the activity as a whole, not to
 * any single user, so only the wordcloud_word_user_rel link is treated as personal data.
 *
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Returns metadata about this plugin's data storage.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this plugin.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'wordcloud_word_user_rel',
            [
                'mapid' => 'privacy:metadata:wordcloud_word_user_rel:mapid',
                'userid' => 'privacy:metadata:wordcloud_word_user_rel:userid',
            ],
            'privacy:metadata:wordcloud_word_user_rel'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} md ON md.id = cm.module AND md.name = :modname
                  JOIN {wordcloud} w ON w.id = cm.instance
                  JOIN {wordcloud_map} wm ON wm.wordcloudid = w.id
                  JOIN {wordcloud_word_user_rel} wur ON wur.mapid = wm.id
                 WHERE wur.userid = :userid";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'wordcloud',
            'userid' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('wordcloud', $context->instanceid);
        if (!$cm) {
            return;
        }

        $sql = "SELECT wur.userid
                  FROM {wordcloud_word_user_rel} wur
                  JOIN {wordcloud_map} wm ON wm.id = wur.mapid
                 WHERE wm.wordcloudid = :wordcloudid";

        $userlist->add_from_sql('userid', $sql, ['wordcloudid' => $cm->instance]);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id('wordcloud', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $sql = "SELECT wm.word, wm.count
                      FROM {wordcloud_word_user_rel} wur
                      JOIN {wordcloud_map} wm ON wm.id = wur.mapid
                     WHERE wm.wordcloudid = :wordcloudid
                       AND wur.userid = :userid";

            $records = $DB->get_records_sql($sql, ['wordcloudid' => $cm->instance, 'userid' => $user->id]);

            if (empty($records)) {
                continue;
            }

            $data = (object) [
                'words' => array_map(static function ($record) {
                    return (object) [
                        'word' => $record->word,
                        'totalcount' => $record->count,
                    ];
                }, array_values($records)),
            ];

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'mod_wordcloud')],
                $data
            );
        }
    }

    /**
     * Delete all user data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('wordcloud', $context->instanceid);
        if (!$cm) {
            return;
        }

        $sql = "mapid IN (SELECT id FROM {wordcloud_map} WHERE wordcloudid = :wordcloudid)";
        $DB->delete_records_select('wordcloud_word_user_rel', $sql, ['wordcloudid' => $cm->instance]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cm = get_coursemodule_from_id('wordcloud', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $sql = "mapid IN (SELECT id FROM {wordcloud_map} WHERE wordcloudid = :wordcloudid) AND userid = :userid";
            $DB->delete_records_select('wordcloud_word_user_rel', $sql, [
                'wordcloudid' => $cm->instance,
                'userid' => $user->id,
            ]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('wordcloud', $context->instanceid);
        if (!$cm) {
            return;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge($inparams, ['wordcloudid' => $cm->instance]);

        $sql = "mapid IN (SELECT id FROM {wordcloud_map} WHERE wordcloudid = :wordcloudid) AND userid $insql";
        $DB->delete_records_select('wordcloud_word_user_rel', $sql, $params);
    }
}
