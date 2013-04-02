<?php

/**
* Controller for question management
* @package mod-magtest
* @category mod
* @author Valéry Frémaux, Etienne Roze
*
* @usecase up
* @usecase down
* @usecase delete
*/

if (!defined('MOODLE_INTERNAL')) {
    die('You cannot access directly to this page');
}

/**
* Requires and includes
*/
include_once $CFG->dirroot."/mod/magtest/listlib.php";

/******************************* Delete a question ****************/
if ($action == 'delete'){
    $qid = required_param('qid', PARAM_INT);

    $DB->delete_records('magtest_useranswer', array('questionid' => $qid));
    $DB->delete_records('magtest_answer', array('questionid' => $qid));
    magtest_list_delete($qid, 'magtest_question');
}
/******************************* Raises a question in the list ****************/
else if ($action == 'up'){
    $qid = required_param('qid', PARAM_INT);

    magtest_list_up($magtest, $qid, 'magtest_question');
}
/******************************* Lowers a question in the list ****************/
else if ($action == 'down'){
    $qid = required_param('qid', PARAM_INT);

    magtest_list_down($magtest, $qid, 'magtest_question');
}
?>