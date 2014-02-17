<?php

/**
* @package mod-magtest
* @category mods
*
*/

require_once "filesystemlib.php";

/**
 * Get all the questions structure of a magtest.
 * Return an array of object questions ( and these answers)
 *
 * @param int $magtestid
 * @return array
 *
 */
function magtest_get_questions($magtestid){
	global $DB;
	
    $questions = $DB->get_records('magtest_question', array('magtestid' => $magtestid), 'sortorder');
    if ($questions) {
        $answers = $DB->get_records('magtest_answer', array('magtestid' => $magtestid));    
        foreach ($answers as $answer ){
            $questions[$answer->questionid]->answers[$answer->id] = $answer; 
        } 
    }
    return $questions;
}

/**
 * Get all the question structure of a magtest question.
 *
 * @param int $magtestid
 * @return array
 *
 */
function magtest_get_question($qid){
	global $DB;
	
    $question = $DB->get_record('magtest_question', array('id' => $qid));
    if ($question) {
        $answers = $DB->get_records('magtest_answer', array('questionid' => $question->id));    
        /*
        foreach ($answers as $answer){
            $question->answers[$answer->id] = $answer; 
        }*/
       $question->answers = $answers;
    }
    return $question;
}

/**
 * gets answers for a question in several modes
 *
 * @param int $magtestid
 * @param int $order 
 * @param boolean $shuffle true if you want change the order of answer
 * @return object
 *
 */
function magtest_get_answers(&$question, $order = 1, $shuffle = false){
	global $DB;
	
    if (!$question) return;

    $question->answers = $DB->get_records('magtest_answer', array('questionid' => $question->id));
    if ($shuffle) {
	    shuffle($question->answers);
    } else {
        $question->ok = false;
    }
}

/**
 * Get all the categories of a magtest.
 * Return an array of object categories 
 *
 * @param int $magtestid
 * @return array
 *
 */
function magtest_get_categories($magtestid){
	global $DB;
	
    if ($categories = $DB->get_records('magtest_category', array('magtestid' => $magtestid), 'sortorder')){
        return $categories;
    }
    return array();
}

/**
 * return the category object of an answer.
 *
 * @param object $answer
 * @return object
 *
 */
function magtest_get_answer_cat($answer) {
	global $DB;
	
    $categorie = $DB->get_record('magtest_category', array('id' => $answer->categoryid));     
    return $categorie;
}

/**
 * Get all the answers in a magtest for one user.
 * Return an array of object useranswers 
 *
 * @param int $magtestid
 * @param array $forusers if set to null, will retrieve answers for all users
 * @return array of records
 *
 */
function magtest_get_useranswers($magtestid, $forusers = null) {
	global $DB;

    if (is_array($forusers)){
        $userlist = implode("','", array_keys($forusers));
    } elseif (is_string($forusers)){
        // admits a single user or a comma separated list
        $userlist = str_replace(',', "','", $forusers);
    }
    
    // @TODO : use more portable IN sql version
    $userclause = (!empty($forusers)) ? " AND userid IN ('$userlist') " : '' ;
    $select = " magtestid = ? {$userclause} ";
    if ($useranswers = $DB->get_records_select('magtest_useranswer', $select, array($magtestid), 'questionid')){
        return $useranswers;
    }
    return array();
}

/**
* get the next unanswered page of questions for a given user
*
*/
function magtest_get_next_questionset(&$magtest, $currentpage){
    global $CFG, $USER, $DB;
    
    // if all questions have answers, test is finished (no next page)
    $sql = "
    	SELECT DISTINCT(COALESCE(mq.id, mua.id)),
    		mq.id,
    		mua.id
    	FROM
    		{magtest_question} mq
    	LEFT JOIN
    		{magtest_useranswer} mua
    	ON
    		mua.questionid = mq.id
    	WHERE
    		mq.magtestid = ? AND
    		(mua.userid = ? OR mua.userid IS NULL) AND
    		mua.id IS NULL
    ";
    if (!$unanswered = $DB->get_records_sql($sql, array($magtest->id, $USER->id))){
    	return false;
    }    
    
    if ($magtest->pagesize){
        $questionset = $DB->get_records('magtest_question', array('magtestid' => $magtest->id), 'sortorder', '*', $currentpage * $magtest->pagesize, $magtest->pagesize);
    } else {
        $questionset = $DB->get_records('magtest_question', array('magtestid' => $magtest->id), 'sortorder');
    }
  
    if ($questionset){
        foreach($questionset as $key => $question){
            $questionset[$key]->answers = $DB->get_records('magtest_answer', array('questionid' => $question->id));
        }
    }
    return $questionset;
}

/**
* get a list of symbol image pathes
* @param reference $magtest
* @param reference $renderingpathbase the path base to be set to access image set
* @uses custom filesystemlib.php common extra library for file system high level access
*/
function magtest_get_symbols(&$magtest, &$renderingpathbase){
    global $CFG;
    
    $symbolpath = '/mod/magtest/pix/symbols';
    $symbolroot = $CFG->dirroot;
    $renderingpathbase = $CFG->wwwroot.'/mod/magtest/pix/symbols/';
    $symbolclasses = filesystem_scan_dir($symbolpath, FS_IGNORE_HIDDEN, FS_ONLY_DIRS, $symbolroot);
    for($i = 0 ; $i < count($symbolclasses) ; $i++){
        $symbolclass = $symbolclasses[$i];
    	if ($symbolclass == 'CVS' || preg_match('/^\./', $symbolclass)) continue;
        $symbols = filesystem_scan_dir($symbolpath.'/'.$symbolclass, FS_IGNORE_HIDDEN, FS_NO_DIRS, $symbolroot);                
        foreach($symbols as $symbol){
            $symboloptions[$symbolclass][$symbolclass.'/'.$symbol] = $symbol;
        }
    }
    return $symboloptions;
}

/**
* get a list of symbol image pathes
* @param reference $magtest
* @param reference $renderingpathbase the path base to be set to access image set
* @uses custom filesystemlib.php common extra library for file system high level access
*/
function magtest_get_symbols_baseurl(&$magtest){
    global $CFG;

    $renderingpathbase = $CFG->wwwroot.'/mod/magtest/pix/symbols/';

    return $renderingpathbase;
}

/**
* Compiles all submitted answers and organise a data structure for reporting
* @param object $magtest (by ref) the current Magtest instance
* @param array $users the users for whom results are compiled
* @param reference $categories the full set of categories with result data
* @param reference $max_cat a structure that records the "winning" category for all users
*/
function magtest_compile_results(&$magtest, &$users, &$categories, &$max_cat){
    global $COURSE, $OUTPUT;

    $usersanswers = magtest_get_useranswers($magtest->id, $users);

    if (! $usersanswers ) {
        echo $OUTPUT->notification(get_string('nouseranswer','magtest'));
        echo $OUTPUT->footer($COURSE);
        exit;
     }

    $categories = magtest_get_categories($magtest->id);
    $questions = magtest_get_questions($magtest->id);
    $count_cat = array();    

    foreach($usersanswers as $useranswer) {
    	if ($magtest->singlechoice){
    		$question = $questions[$useranswer->questionid];
    		foreach($question->answers as $answer){
    			if ($useranswer->answerid == 1){
			        $cat = $categories[$answer->categoryid];
	            	$count_cat[$useranswer->userid][$cat->id] = 0 + @$count_cat[$useranswer->userid][$cat->id] + $answer->weight ;
	            }
    		}
    	} else {
    		$question = $questions[$useranswer->questionid];
    		$answer = $question->answers[$useranswer->answerid];
	        $cat = $categories[$answer->categoryid];    

	        // aggregate scores
            $count_cat[$useranswer->userid][$cat->id] = 0 + @$count_cat[$useranswer->userid][$cat->id] + $answer->weight ;
	    }
    }

    /// get max for each user and organize them in categories
    foreach($users as $user){
    	$max_cat[$user->id] = new StdClass();
        $max_cat[$user->id]->score = 0;
        $max_cat[$user->id]->catid = 0;
        foreach($categories as $cat){
            if (@$count_cat[$user->id][$cat->id] > $max_cat[$user->id]->score){
                $max_cat[$user->id]->score = $count_cat[$user->id][$cat->id];
                $categories[$cat->id]->users[] = $user->id;
            }
        }
    }            
}

/**
*
* @param object $magtest
* @param array $users
*/
function magtest_get_unsubmitted_users(&$magtest, &$users){
    global $CFG, $DB;

    if (empty($users)) return false;

    $userlist = implode("','", array_keys($users));

    // searches everyone that is in submitted subset that HAS NOT
    // records in user answers
    $sql = "
        SELECT
            u.id,
            u.firstname,
            u.lastname,
            u.email,
            u.emailstop,
            u.picture,
            u.mnethostid,
            u.imagealt
        FROM 
            {user} u
        WHERE 
            u.id IN ('$userlist') AND
            u.id NOT IN ( 
                SELECT DISTINCT
                    userid 
                FROM
                    {magtest_useranswer}
                WHERE
                    magtestid = {$magtest->id})
    ";
    if ($missings = $DB->get_records_sql($sql)){
        return $missings;
    }
    return array();
}

/**
* tests if the configuration of the magtest is "playable"
* @param reference $magtest
* @return true if the configuration binds to a playable test
*/
function magtest_test_configuration(&$magtest){
	global $DB;
	
    // checks we have categories in test
    $catcount = $DB->count_records('magtest_category', array('magtestid' => $magtest->id));    
    if ($catcount == 0) return false;

    // checks we have questions in test
    $questioncount = $DB->count_records('magtest_question', array('magtestid' => $magtest->id));    
    if ($questioncount == 0) return false;

    return true;    
}
