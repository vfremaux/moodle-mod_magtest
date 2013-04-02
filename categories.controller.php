<?php

/**
* Controller for "categories" list
* Keep category global use case and defer add and update to moodle form sidepath
* 
* @package    mod-magtest
* @category   mod
* @author     Valery Fremaux <valery.fremaux@club-internet.fr>
* @contributors   Etienne Roze
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
* @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
* @see        categories.php for view.
* @usecase    deletecategory
* @usecase    raisecategory
* @usecase    lowercategory
**/

/**
*
*/
require_once $CFG->dirroot.'/mod/magtest/listlib.php';



if (!defined('MOODLE_INTERNAL')) {
    die('You cannot access directly to this page');
}
   
/******************************************* delete a category ***********************/
if ($action == 'deletecategory'){
    $catid = required_param('catid', PARAM_INT);

    $answers = $DB->get_records('magtest_answer', array('categoryid' => $catid), '', 'id,id');
    if (!empty($answers)){
        $DB->delete_records('magtest_answer', array('categoryid' => $catid));
        $deletedanswerslist = implode("','", array_keys($answers));
        $DB->delete_records_select('magtest_useranswer', "answerid IN ('$deletedanswerslist')");
    }
    magtest_list_delete($catid, 'magtest_category');
}
/******************************************* raises a category ***********************/
if ($action == 'raisecategory'){
    $catid = required_param('catid', PARAM_INT);
    magtest_list_up($magtest, $catid, 'magtest_category');
}
/******************************************* lower a category ***********************/
if ($action == 'lowercategory'){

    $catid = required_param('catid', PARAM_INT);
    magtest_list_down($magtest, $catid, 'magtest_category');
}

?>