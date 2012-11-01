<?php  // $Id: listlib.php,v 1.1 2011-09-16 09:23:29 vf Exp $

/**
* Project : Magazine test 
*
* A special lib for handling list-shaped entities.
* Assume ordering is performed by a sortorder int field,
* starting at 1.
*
* @package mod-magtest
* @category mod
* @author So Gerard (EISTI 2002)
* @date 2008/03/03
* @contributors Valery Fremaux (France) (admin@www.ethnoinformatique.fr)
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
*/

/// Library of ordered list dedicated operations
// inspired from techproject treelib.php

/**
* deletes a node and keeps the ordering consistent
* @param int $id the id of the deleted item
* @param string $table the ordered table
* @return the deleted id
*/
function magtest_list_delete($id, $table){
	global $CFG;

    if (empty($id)) return null;    

	$res =  get_record($table, 'id', $id);
	if (!$res) return;
	
	if ($res->sortorder > 1){
	    $previous = get_record($table, 'id', $id, 'sortorder', $res->sortorder - 1);
	}
	
	// deleting current node
	delete_records($table, 'id', $res->id);
	if (!empty($previous)){
    	magtest_list_updateordering($previous->magtestid, $previous->id, $table);
    }
	return $id;
}

/**
* updates ordering of a list, reordering 
* all subsequent siblings. 
* @param reference $magtest a context to get records from
* @param int $id the node from where to reorder
* @param string $table the ordered table
*/
function magtest_list_updateordering(&$magtest, $id, $table){
	global $CFG;

    if (is_int($magtest)){
        $magtest->id = $magtest;
    }

	// getting ordering value of the current node
	$res =  get_record($table, 'id', $id);
	if (!$res) return;

	// getting subsequent nodes that are upper
	$query = "
	    SELECT 
	        id   
	    FROM 
	        {$CFG->prefix}$table
	    WHERE 
	        sortorder > {$res->sortorder} AND
	        magtestid = {$magtest->id}
	    ORDER BY 
	        sortorder
	";

	// reordering subsequent nodes using an object
	if( $nextsubs = get_record_sql($query)) {
	    $ordering = $res->sortorder + 1;
		foreach($nextsubs as $asub){
			$objet->id = $asub->id;
			$objet->sortorder = $ordering;
			update_record($table, $objet);
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
function magtest_list_up(&$magtest, $id, $table){

	$res = get_record($table, 'id', $id);
	if($res->sortorder > 1){
		$previousorder = $res->sortorder - 1;
		$previous =  get_record($table, 'magtestid', $magtest->id, 'sortorder', $previousorder);

        // swapping
		$res->sortorder--;
		update_record("$table", $res);

		$previous->sortorder++;
		update_record("$table", $previous);
	}
}

/**
* lowers a node in the list. this is done by swapping ordering.
* @param reference $magtest a context to get records from
* @param int $id the node id to be lowered
* @param string $table the table-list where to perform swap
*/
function magtest_list_down(&$magtest, $id, $table){

	$res =  get_record($table, 'id', $id);
	$maxordering = magtest_get_max_ordering($magtest, $table);
	if($res->sortorder < $maxordering){
		$nextorder = $res->sortorder + 1;
		$next =  get_record($table, 'magtestid', $magtest->id, 'sortorder', $nextorder);

        // swapping
		$res->sortorder++;
		update_record("$table", $res);

		$next->sortorder--;
		update_record("$table", $next);
	}
}

/**
* get the max ordering available in sequence
* @param reference $magtest the context to get records from
* @param table the table-list where to search
* @return integer the max ordering found
*/
function magtest_get_max_ordering(&$magtest, $table){
    global $CFG;

    return 0 + get_field_select("$table", 'MAX(sortorder)', "magtestid = {$magtest->id} GROUP BY magtestid ");
}

?>