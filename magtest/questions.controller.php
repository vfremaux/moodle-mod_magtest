<?php

/**
* Controller for question management
* @package mod-magtest
* @category mod
* @author Valéry Frémaux, Etienne Roze
*
* @usecase addquestion
* @usecase doaddquestion
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

/************************************** Add a question *********************/
if ($action == 'addquestion'){
    $command = 'doaddquestion';
    include $CFG->dirroot."/mod/magtest/type/{$magtest->type}/addupdatequestion.html";
    return -1;
}
/************************************** Updates a question *********************/
if ($action == 'updatequestion'){
    $command = 'doupdatequestion';
    $qid = required_param('qid', PARAM_INT);
    $question = magtest_get_question($qid);
    include $CFG->dirroot."/mod/magtest/type/{$magtest->type}/addupdatequestion.html";
    return -1;
}
/************************************** Add a question *********************/
else if ($action == 'doaddquestion'){

    $lastorder = magtest_get_max_ordering($magtest, 'magtest_question');
    $question->questiontext = required_param('questiontext', PARAM_CLEANHTML);
    $question->format = required_param('format', PARAM_INT);
    $question->magtestid = $magtest->id;
    $question->sortorder = $lastorder + 1;
    if ($newid = $DB->insert_record('magtest_question', $question)){
        $answerkeys = preg_grep("/^answer/", array_keys($_POST));
        foreach($answerkeys as $akey){
            preg_match('/answer(\d+)(.*)/', $akey, $matches);
            $aid = $matches[1];
            $suffix = $matches[2];
            $answer = new StdClass;
            $answer->questionid = $newid;
            $answer->magtestid = $magtest->id;
            $answer->answertext = required_param("answer{$aid}{$suffix}", PARAM_CLEANHTML);
            $answer->format = required_param("format{$aid}{$suffix}", PARAM_INT);
            $answer->helper = required_param("helper{$aid}{$suffix}", PARAM_CLEANHTML);
            $answer->helperformat = required_param("helperformat{$aid}{$suffix}", PARAM_INT);
            $answer->categoryid = required_param("cat{$aid}{$suffix}", PARAM_INT);
            if ($magtest->weighted){
                $answer->weight = required_param("weight{$aid}{$suffix}", PARAM_NUMBER);
            } else {
                $answer->weight = 1.0;
            }
            $DB->insert_record('magtest_answer', $answer);
        }
    }
}
/************************************** Updates a question *********************/
else if ($action == 'doupdatequestion'){

    $lastorder = magtest_get_max_ordering($magtest, 'magtest_question');
    $question->id = required_param('qid', PARAM_INT);
    $question->questiontext = required_param('questiontext', PARAM_CLEANHTML);
    $question->format = required_param('format', PARAM_INT);
    $question->magtestid = $magtest->id;
    $updatedids = array();
    if ($DB->update_record('magtest_question', $question)){
        $answerkeys = preg_grep("/^answer/", array_keys($_POST));
        foreach($answerkeys as $akey){
            preg_match('/answer(\\d+)(.*)/', $akey, $matches);
            $aid = $matches[1];
            $suffix = $matches[2];
            $answer = new StdClass;
            $answer->id = $aid;
            $answer->answertext = required_param("answer{$aid}{$suffix}", PARAM_CLEANHTML);
            $answer->format = optional_param("format{$aid}{$suffix}", PARAM_INT);
            $answer->helper = required_param("helper{$aid}{$suffix}", PARAM_CLEANHTML);
            $answer->helperformat = optional_param("helperformat{$aid}{$suffix}", PARAM_INT);
            $answer->categoryid = required_param("cat{$aid}{$suffix}", PARAM_INT);
            if ($magtest->weighted){
                $answer->weight = required_param("weight{$aid}{$suffix}", PARAM_NUMBER);
            } else {
                $answer->weight = 1.0;
            }
            if ($aid){
                $DB->update_record('magtest_answer', $answer);
                $updatedids[] = $answer->id;
            } else {
                $answer->magtestid = $magtest->id;
                $answer->questionid = $question->id;
                if ($newid = $DB->insert_record('magtest_answer', $answer)){
                    $updatedids[] = $newid;
                } else {
                    print_error('erroranswerinsert', 'magtest', $question->id);
                }
            }
        }
        // purge spare answers.
        $updatedidlist = implode("','", $updatedids);
        $DB->delete_records_select('magtest_answer', "questionid = $question->id AND id NOT IN ('$updatedidlist') ");
    } else {
        print_error('errorquestionupdate', 'magtest', $question->id);
    }
}
/******************************* Delete a question ****************/
else if ($action == 'delete'){
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