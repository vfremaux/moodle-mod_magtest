<?php  // $Id: lib.php,v 1.1 2011-09-16 09:23:29 vf Exp $

/**
 * Library of functions and constants for module magtest
 *
 * @author 
 * @package mod-magtest
 * @category mod
 **/

/// (replace magtest with the name of your module and delete this line)

/**
* includes and defines
*/
define('MAGTEST_RESETFORM_RESET', 'magtest_reset_data_');

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted magtest record
 **/
function magtest_add_instance($magtest) {
    
    $magtest->timemodified = time();

    $return = insert_record('magtest', $magtest);

    return $return;
}

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function magtest_update_instance($magtest) {

    $magtest->timemodified = time();
    $magtest->id = $magtest->instance;

    if (!isset($magtest->starttimeenable)) $magtest->starttimeenable = 0;
    if (!isset($magtest->endtimeenable)) $magtest->endtimeenable = 0;
    if (!isset($magtest->usemakegroups)) $magtest->usemakegroups = 0;
    if (!isset($magtest->allowreplay)) $magtest->allowreplay = 0;

    return update_record('magtest', $magtest);
}

/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it. 
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function magtest_delete_instance($id) {

    if (! $magtest = get_record('magtest', 'id', "$id")) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    

    if (! delete_records('magtest', 'id', "$magtest->id")) {
        $result = false;
    }

    return $result;
}

/**
 * Return a small object with summary information about what a 
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function magtest_user_outline($course, $user, $mod, $magtest) {

	if ($answers = $get_records('magtest_useranswer', 'userid', $user->id)){
		$firstanswer = array_pop($answers);
	    $result = new stdClass();
	    $result->info = get_string('magtestattempted', 'magtest') . ': ' . userdate($firstanswer->timeanswered);
	} else {
		return null;
	}
	
    return $result;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function magtest_user_complete($course, $user, $mod, $magtest) {

	if ($answers = get_records('magtest_useranswer', 'userid', $user->id)){
		$firstanswer = array_pop($answers);
	    echo get_string('magtestattempted', 'magtest') . ': ' . userdate($firstanswer->timeanswered);
	}
	
	if ($accesses = get_records_select('log', " userid = $user->id AND module = 'magtest' and action = 'view' ")){
		echo '<br/>';
	    echo get_string('magtestaccesses', 'magtest', count($accesses)) ;
	}

	return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in magtest activities and print it out. 
 * Return true if there was output, or false is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function magtest_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function magtest_cron () {
    global $CFG;

    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $magtestid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function magtest_grades($magtestid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of magtest. Must include every user involved
 * in the instance, independent of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $magtestid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function magtest_get_participants($magtestid) {
    global $CFG;
    
    $sql = "
        SELECT
            u.*
        FROM
            {$CFG->prefix}user u,
            {$CFG->prefix}magtest_useranswer ua
        WHERE            
            u.id = ua.userid AND
            ua.magtestid = {$magtestid}
    ";
        
    if (!$records = get_records_sql($sql)){
        return false;
    }
    return $records;
}

/**
 * This function returns if a scale is being used by one magtest
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $magtestid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function magtest_scale_used ($magtestid,$scaleid) {
    $return = false;

    //$rec = get_record("magtest","id","$magtestid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all responses from the specified magtest
 * and clean up any related data.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function magtest_reset_userdata($data) {
    global $CFG;
    
    $status = array();
    $componentstr = get_string('modulenameplural', 'magtest');
    
    //get the relevant entries from $data and drop answers
    foreach($data as $key => $value) {
        if (preg_match('/^'.MAGTEST_RESETFORM_RESET."(\\d+)/", $key, $matches)){
            $magtestid = $matches[1];
            $magtest = get_record('magtest', 'id', $magtestid);
            delete_records('magtest_useranswer', 'magtestid', $magtestid);
            $status[] = array('component' => $componentstr.':'.$magtest->name, 'item' => get_string('resetting_data','magtest'), 'error' => false);
        }
    }
    
    return $status;
}

/** 
 * Called by course/reset.php and shows the formdata by coursereset.
 * it prints checkboxes for each feedback available at the given course
 * there are two checkboxes: 1) delete userdata and keep the feedback 2) delete userdata and drop the feedback
 * @param object $course
 * @return void
 * // OLD WAY OBSOLOETE IN 1.9
 */
function magtest_reset_course_form($course) {

    echo get_string('resetting_magtests', 'magtest'); 
    echo ':<br />';

    if (!$magtests = get_records('magtest', 'course', $course->id, 'name')){
         return;
    }
    
    foreach($magtets as $magtest) {
        echo '<p>';
        echo get_string('name').': '.$magtest->name.'<br />';
        print_checkbox(MAGTEST_RESETFORM_RESET.$magtest->id, 1, true, get_string('resetting_data','magtest'), '', '');  echo '<br />';
        echo '</p>';
    }
}

/**
 * Called by course/reset.php
 * @param $mform form passed by reference
 */
function magtest_reset_course_form_definition(&$mform) {
    global $COURSE;

    $mform->addElement('header', 'magtestheader', get_string('modulenameplural', 'magtest'));
    
    if(!$magtests = get_records('magtest', 'course', $COURSE->id)){
        return;
    }

    $mform->addElement('static', 'hint', get_string('resetting_data','magtest'));
    foreach($magtests as $magtest) {
        if ($hasanswers = count_records('magtest_useranswer', 'magtestid', $magtest->id)){
            $mform->addElement('checkbox', MAGTEST_RESETFORM_RESET.$magtest->id, $magtest->name);
        }
    }
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other magtest functions go here.  Each of them must have a name that 
/// starts with magtest_

/**
* tells a certificate module if the activity has been done or not
*
*/
function magtest_activity_completed(&$cm, $userid) {

	$magtestid = get_field('course_modules', 'instance', 'id', $cm->id);
	if (count_records('magtest_useranswer', 'userid', $userid)){
		return true;
	}
	return false;
}

?>
