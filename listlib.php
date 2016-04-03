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

defined('MOODLE_INTERNAL') || die();

/**
 * A special lib for handling list-shaped entities.
 * Assume ordering is performed by a sortorder int field,
 * starting at 1.
 *
 * @package mod_magtest
 * @category mod
 * @author So Gerard (EISTI 2002)
 * @date 2008/03/03
 * @contributors Valery Fremaux (France) (admin@www.ethnoinformatique.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

// Library of ordered list dedicated operations.
// inspired from techproject treelib.php.

/**
 * deletes a node and keeps the ordering consistent
 * @param int $id the id of the deleted item
 * @param string $table the ordered table
 * @return the deleted id
 */
function magtest_list_delete($id, $table) {
    global $CFG,$DB;

    if (empty($id)) {
        return null;
    }

    $res =  $DB->get_record($table, array('id' => $id));
    if (!$res) return;
    if ($res->sortorder > 1) {
        $previous = $DB->get_record($table, array('id' => $id, 'sortorder' => $res->sortorder - 1));
    }

    // Deleting current node.
    $DB->delete_records($table, array('id' => $res->id));
    if (!empty($previous)) {
        magtest_list_updateordering($previous->magtestid, $previous->id, $table);
    }
    return $id;
}

/**
 * Updates ordering of a list, reordering 
 * all subsequent siblings. 
 * @param reference $magtest a context to get records from
 * @param int $id the node from where to reorder
 * @param string $table the ordered table
 */
function magtest_list_updateordering(&$magtest, $id, $table) {
    global $CFG, $DB;

    if (is_int($magtest)) {
        $magtest->id = $magtest;
    }

    // Getting ordering value of the current node.
    $res =  $DB->get_record($table, array('id' => $id));
    if (!$res) {
        return;
    }

    // Getting subsequent nodes that are upper.
    $query = "
        SELECT 
            id   
        FROM 
            {$table}
        WHERE 
            sortorder > ? AND
            magtestid = ?
        ORDER BY 
            sortorder
    ";

    // Reordering subsequent nodes using an object.
    if ($nextsubs = $DB->get_record_sql($query, array($res->sortorder, $magtest->id))) {
        $ordering = $res->sortorder + 1;
        foreach ($nextsubs as $asub) {
            $objet->id = $asub->id;
            $objet->sortorder = $ordering;
            $DB->update_record($table, $objet);
            $ordering++;
        }
    }
}

/**
 * raises a node in the list, reordering all what needed
 * @param reference $magtest a context to get records from
 * @param int $id the id of the raised node
 * @param string $table the table-list name where to operate
 * @return void
 */
function magtest_list_up(&$magtest, $id, $table) {
    global $DB;

    $res = $DB->get_record($table, array('id' => $id));
    if ($res->sortorder > 1) {
        $previousorder = $res->sortorder - 1;
        $previous =  $DB->get_record($table, array('magtestid' => $magtest->id, 'sortorder' => $previousorder));

        // Swapping.
        $res->sortorder--;
        $DB->update_record("$table", $res);

        $previous->sortorder++;
        $DB->update_record("$table", $previous);
    }
}

/**
 * lowers a node in the list. this is done by swapping ordering.
 * @param reference $magtest a context to get records from
 * @param int $id the node id to be lowered
 * @param string $table the table-list where to perform swap
 */
function magtest_list_down(&$magtest, $id, $table) {
    global $DB;

    $res =  $DB->get_record($table, array('id' => $id));
    $maxordering = magtest_get_max_ordering($magtest, $table);

    if ($res->sortorder < $maxordering) {
        $nextorder = $res->sortorder + 1;
        $next =  $DB->get_record($table, array('magtestid' => $magtest->id, 'sortorder' => $nextorder));

        // Swapping.
        $res->sortorder++;
        $DB->update_record("$table", $res);

        $next->sortorder--;
        $DB->update_record("$table", $next);
    }
}

/**
 * Get the max ordering available in sequence
 * @param reference $magtest the context to get records from
 * @param table the table-list where to search
 * @return integer the max ordering found
 */
function magtest_get_max_ordering(&$magtest, $table) {
    global $CFG, $DB;

    return 0 + $DB->get_field_select("$table", 'MAX(sortorder)', "magtestid = {$magtest->id} GROUP BY magtestid ");
}
