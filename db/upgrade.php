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
 * Wordcloud db upgrade
 *
 * @package    mod_wordcloud
 * @copyright  2020 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * xmldb_streamlti_upgrade is the function that upgrades
 * the streamlti module database when is needed
 *
 * This function is automaticly called when version number in
 * version.php changes.
 *
 * @param int $oldversion New old version number.
 *
 * @return boolean
 */
function xmldb_wordcloud_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020100500) {
        $table = new xmldb_table('wordcloud');
        $field = new xmldb_field('lastwordchange', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2020100500, 'wordcloud');
    }

    if ($oldversion < 2021020401) {
        $table = new xmldb_table('wordcloud_map');
        $field = new xmldb_field('word', XMLDB_TYPE_CHAR, '160', null, XMLDB_NOTNULL, null);
        $key = new xmldb_key('uk_word', 'unique', ['wordcloudid', 'word']);

        $dbman->drop_key($table, $key);
        $dbman->change_field_precision($table, $field);
        $dbman->add_key($table, $key);

        upgrade_mod_savepoint(true, 2021020401, 'wordcloud');
    }

    if ($oldversion < 2021120900) {
        $table = new xmldb_table('wordcloud');
        $field = new xmldb_field('timeopen', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timeclose', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2021120900, 'wordcloud');
    }

    if ($oldversion < 2022033101) {
        $table = new xmldb_table('wordcloud');
        $field = new xmldb_field('usedivcolor');

        if ($dbman->field_exists($table, $field)) {
            $field->set_attributes(XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $dbman->rename_field($table, $field, 'usemonocolor');
        } else {
            $field = new xmldb_field('usemonocolor', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2022033101, 'wordcloud');
    }

    if ($oldversion < 2022051802) {
        $table = new xmldb_table('wordcloud_map');
        $field = new xmldb_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $key = new xmldb_key('uk_word', 'unique', ['wordcloudid', 'word']);
        $dbman->drop_key($table, $key);

        $key = new xmldb_key('uk_word', 'unique', ['wordcloudid', 'groupid', 'word']);
        $dbman->add_key($table, $key);

        upgrade_mod_savepoint(true, 2022051802, 'wordcloud');
    }

    if ($oldversion < 2022070100) {

        // Define field monocolor to be added to wordcloud.
        $table = new xmldb_table('wordcloud');
        $field = new xmldb_field('monocolor', XMLDB_TYPE_INTEGER, '1', null, null, null, '1', 'usemonocolor');

        // Conditionally launch add field monocolor.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field monocolorhex to be added to wordcloud.
        $field = new xmldb_field('monocolorhex', XMLDB_TYPE_CHAR, '6', null, null, null, '000000', 'monocolor');

        // Conditionally launch add field monocolorhex.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Wordcloud savepoint reached.
        upgrade_mod_savepoint(true, 2022070100, 'wordcloud');
    }

    if ($oldversion < 2023100801) {
        $table = new xmldb_table('wordcloud_word_user_rel');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('mapid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('uk_word_user', XMLDB_KEY_FOREIGN, ['mapid', 'userid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('wordcloud');
        $field = new xmldb_field('visibility', XMLDB_TYPE_INTEGER, '1', null, null, null, '1');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('completionsubmits', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2023100801, 'wordcloud');
    }

    return true;
}
