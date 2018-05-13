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

class magtest {

    /**
     * insert a new test category
     *
     * @param mixed $magtestid
     * @param mixed $name
     * @param mixed $descriptionformat
     * @param mixed $description
     * @param mixed $result
     * @param mixed $sortorder
     * @param mixed $symbol
     */
    public static function add_category($magtestid, $magtestrec) {
        global $DB;

        $lastorder = $DB->get_field('magtest_category', 'MAX(sortorder)', array('magtestid' => $magtestid));
        $magtestrec->magtestid = $magtestid;
        $magtestrec->sortorder = ++$lastorder;

        $newid = $DB->insert_record('magtest_category', $magtestrec);
        return $newid;
    }

    /**
     * delete a test category given the category id;
     *
     * @param mixed $catid
     */
    public static function delete_category($catid) {
        global $DB;

        $DB->delete_records('magtest_category', array('id' => $catid));
    }

    /**
     * update test category
     *
     * @param mixed $catid
     * @param mixed $name
     * @param mixed $descriptionformat
     * @param mixed $description
     * @param mixed $result
     * @param mixed $sortorder
     * @param mixed $symbol
     */
    public static function update_category($catid, $name, $descriptionformat, $description, $result, $sortorder, $symbol) {
        global $DB;

        $magtestrec = new stdClass();
        $magtestrec->id = $catid;
        $magtestrec->magtestid = $magtestid;
        $magtestrec->name = $name;
        $magtestrec->description = $description;
        $magtestrec->descriptionformat = $descriptionformat;
        $magtestrec->result = $result;
        $magtestrec->sortorder = $sortorder;
        $magtestrec->symbol = $symbol;

        $newid = $DB->update_record('magtest_category', $magtestrec);
        return $newid;
    }
}
