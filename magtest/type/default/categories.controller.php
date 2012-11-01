<?php

/**
* Controller for "categories"
* 
* @package    mod-magtest
* @category   mod
* @author     Valery Fremaux <valery.fremaux@club-internet.fr>
* @contributors   Etienne Roze
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
* @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
* @see        categories.php for view.
* @usecase    addcategories (form)
* @usecase    editcategory (form)
* @usecase    doaddcategories
* @usecase    doupdatecategory
* @usecase    deletecategory
*/

/**
*
*/
require_once $CFG->dirroot.'/mod/magtest/listlib.php';


if (!defined('MOODLE_INTERNAL')) {
    error('You cannot access directly to this page');
}

/************************************ print add categories form ***********************/
if ($action == 'addcategories'){
    $cmd = 'addcategories'; // preppends 'do' in form
    $howmany = required_param('catnum', PARAM_INT);
    include "$include/addcategories.html";
    return -1;
}
/************************************ print edit a category form ***********************/
if ($action == 'editcategory'){
    $cmd = 'updatecategory'; // preppends 'do' in form
    $catid = required_param('catid', PARAM_INT);
    $categories[0] = get_record('magtest_category', 'id', $catid);
    $howmany = 1;
    include "$include/addcategories.html";
    return -1;
}
/******************************************* add a category ***********************/
if ($action == 'doaddcategories'){
    $categorynames = required_param('name', PARAM_CLEANHTML);
    $categorydescs = required_param('description', PARAM_CLEANHTML);
    $categoryresults = required_param('result', PARAM_CLEANHTML);
    $categoryformats = required_param('format', PARAM_CLEANHTML);
    $categorysymbols = required_param('symbol', PARAM_TEXT);
    $outputgroupnames = optional_param('outputgroupname', PARAM_CLEANHTML);
    $outputgroupdescs = optional_param('outputgroupdesc', PARAM_CLEANHTML);

    $maxorder = magtest_get_max_ordering($magtest, 'magtest_category');
    
    if (is_array($categorynames)){
        for($i = 0 ; $i < count($categorynames) ; $i++){
            $category = new StdClass;
            $category->magtestid = $magtest->id;
            $category->name = addslashes($categorynames[$i]);
            $category->description = addslashes($categorydescs[$i]);
            $category->format = $categoryformats[$i];
            $category->result = addslashes($categoryresults[$i]);
            $category->symbol = $categorysymbols[$i];
            if ($magtest->usemakegroups){
                $category->outputgroupname = addslashes($outputgroupnames[$i]);
                $category->outputgroupdesc = addslashes($outputgroupdescs[$i]);
            }
            $category->sortorder = $maxorder + 1;
            if ($existing = get_record('magtest_category', 'name', $category->name, 'magtestid', $magtest->id)){
                $category->id = $existing->id;
                update_record('magtest_category', $category);
            } else {
                insert_record('magtest_category', $category);
            }
        }
    }
}
/******************************************* edit a category ***********************/
if ($action == 'doupdatecategory'){
    $categorynames = required_param('name', PARAM_CLEANHTML);
    $categorydescs = required_param('description', PARAM_CLEANHTML);
    $categoryformats = required_param('format', PARAM_CLEANHTML);
    $categoryresults = required_param('result', PARAM_CLEANHTML);
    $categorysymbols = required_param('symbol', PARAM_TEXT);
    $outputgroupnames = optional_param('outputgroupname', PARAM_CLEANHTML);
    $outputgroupdescs = optional_param('outputgroupdesc', PARAM_CLEANHTML);
    
    $category->id = required_param('catid', PARAM_INT);
    $category->name = addslashes($categorynames[0]);
    $category->description = addslashes($categorydescs[0]);
    $category->format = $categoryformats[0];
    $category->result = addslashes($categoryresults[0]);
    if ($magtest->usemakegroups){
        $category->outputgroupname = addslashes($outputgroupnames[0]);
        $category->outputgroupdesc = addslashes($outputgroupdescs[0]);
    }
    $category->symbol = $categorysymbols[0];

    if (!update_record('magtest_category', $category)){
        error("Could not update magtest category $category->id");
    }
}
/******************************************* delete a category ***********************/
if ($action == 'deletecategory'){
    $catid = required_param('catid', PARAM_INT);

    $answers = get_records('magtest_answer', 'categoryid', $catid, '', 'id,id');
    if (!empty($answers)){
        delete_records('magtest_answer', 'categoryid', $catid);
        $deletedanswerslist = implode("','", array_keys($answers));
        delete_records_select('magtest_useranswer', "answerid IN ('$deletedanswerslist')");
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