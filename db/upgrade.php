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
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_magtest_upgrade($oldversion = 0) {
    global $CFG, $DB;

    $result = true;

    $dbman = $DB->get_manager();

    if ($oldversion < 2008053100) {

        // Define field course to be added to magtest.
        $table = new xmldb_table('magtest');
        $field = new xmldb_field('course');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'id');

        // Launch add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2008053100, 'magtest');
    }

    if ($oldversion < 2009053100) {

        $table = new xmldb_table('magtest_answer');
        $field = new xmldb_field('helper');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null,  null, 'format');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('helperformat');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'helper');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('weight');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '10', null, XMLDB_NOTNULL, null, 1, 'categoryid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2009053100, 'magtest');
    }

    // Moodle 2.0 upgrade horizon.

    if ($oldversion < 2012103100) {

        // Define field course to be added to magtest.
        $table = new xmldb_table('magtest');
        $field = new xmldb_field('description');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null,  null, 'id');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'intro', false);
        }

        $field = new xmldb_field('introformat');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'intro');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('magtest_answer');
        $field = new xmldb_field('format');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'answertext');

        if ($dbman->field_exists($table, $field)) {
            // Pre-fix possible old NULL values.
            $sql = "
                UPDATE
                    {magtest_answer}
                SET
                    format = 0
                WHERE
                    format IS NULL
            ";
            $DB->execute($sql);

            $dbman->rename_field($table, $field, 'answertextformat', false);
        }

        $table = new xmldb_table('magtest_question');
        $field = new xmldb_field('format');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'questiontext');

        if ($dbman->field_exists($table, $field)) {
            // Pre-fix possible old NULL values.
            $sql = "
                UPDATE
                    {magtest_question}
                SET
                    format = 0
                WHERE
                    format IS NULL
            ";
            $DB->execute($sql);

            $dbman->rename_field($table, $field, 'questiontextformat', false);
        }

        $table = new xmldb_table('magtest_category');

        $field = new xmldb_field('format');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'name');
        if ($dbman->field_exists($table, $field)) {

            // Pre-fix possible old NULL values.
            $sql = "
                UPDATE
                    {magtest_category}
                SET
                    format = 0
                WHERE
                    format IS NULL
            ";
            $DB->execute($sql);

            $dbman->rename_field($table, $field, 'descriptionformat', false);
        }

        $field = new xmldb_field('outputgroupname');
        $field->set_attributes(XMLDB_TYPE_CHAR, '32', null, null, null, null, 'result');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('outputgroupdesc');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null,  null, 'outputgroupname');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2012103100, 'magtest');
    }

    if ($oldversion < 2014012802) {

        $table = new xmldb_table('magtest');
        $field = new xmldb_field('singlechoice');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'allowreplay');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2014012802, 'magtest');
    }

    if ($oldversion < 2018042400) {

        $table = new xmldb_table('magtest');
        $field = new xmldb_field('usesetprofile');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'usemakegroups');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('magtest_category');
        $field = new xmldb_field('outputfieldname');
        $field->set_attributes(XMLDB_TYPE_CHAR, '64', null, null, null, null, 'outputgroupdesc');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('magtest_category');
        $field = new xmldb_field('outputfieldvalue');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, 'outputfieldname');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2018042400, 'magtest');
    }

    return $result;
}
