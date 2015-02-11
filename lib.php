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
 * Library of functions and constants for module magtest
 *
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @package mod-magtest
 * @category mod
 **/

define('MAGTEST_RESETFORM_RESET', 'magtest_reset_data_');

/**
 * List of features supported in Vodeclic module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function magtest_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_OTHER;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

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
    global $DB;

    $magtest->timemodified = time();
    
    if (!empty($magtest->singlechoice)) {
        $magtest->weighted = 1;
    }

    $return = $DB->insert_record('magtest', $magtest);

    return $return;
}

function pix_url() {
    return '';
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
    global $DB;
    
    $oldmode = $DB->get_field('magtest', 'singlechoice', array('id' => $magtest->instance));

    // If changing mode, we need delete all previous user dataas they are NOT relevant any more
    // @TODO : add notification in mod_form to alert users...
    if ($oldmode != $magtest->singlechoice) {
        $DB->delete_records('magtest_useranswer', array('magtestid' => $magtest->instance));
    }
    
    $magtest->timemodified = time();
    $magtest->id = $magtest->instance;

    if (!empty($magtest->singlechoice)) {
        $magtest->weighted = 1;
    }

    if (!isset($magtest->starttimeenable)) $magtest->starttimeenable = 0;
    if (!isset($magtest->endtimeenable)) $magtest->endtimeenable = 0;
    if (!isset($magtest->usemakegroups)) $magtest->usemakegroups = 0;
    if (!isset($magtest->allowreplay)) $magtest->allowreplay = 0;
    if (!isset($magtest->weighted)) $magtest->weighted = 0;
    if (!isset($magtest->singlechoice)) $magtest->singlechoice = 0;

    return $DB->update_record('magtest', $magtest);
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
    global $DB;

    if (! $magtest = $DB->get_record('magtest', array('id' => "$id"))) {
        return false;
    }

    if (!$cm = get_coursemodule_from_instance('magtest', $magtest->id)) {
        return false;
    }

    $context = context_module::instance($cm->id);

    $result = true;

    # Delete any dependent records here #


    if (! $DB->delete_records('magtest', array('id' => "$magtest->id"))) {
        $result = false;
    }

    // delete all files attached to this context
    $fs = get_file_storage();
    $fs->delete_area_files($context->id);

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
    global $DB;

    if ($answers = $DB->get_records('magtest_useranswer', array('userid' => $user->id))){
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
    global $DB;

    if ($answers = $DB->get_records('magtest_useranswer', array('userid' => $user->id))){
        $firstanswer = array_pop($answers);
        echo get_string('magtestattempted', 'magtest') . ': ' . userdate($firstanswer->timeanswered);
    }

    if ($accesses = $DB->get_records_select('log', " userid = ? AND module = 'magtest' and action = 'view' ", array($user->id))){
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
 *
 **/
function magtest_scale_used_anywhere($scaleid) {
    global $DB;

    return false;
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
    global $CFG, $DB;

    $sql = "
        SELECT DISTINCT
            u.*
        FROM
            {user} u,
            {magtest_useranswer} ua
        WHERE
            u.id = ua.userid AND
            ua.magtestid = {$magtestid}
    ";
    if (!$records = $DB->get_records_sql($sql)){
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
    global $CFG, $DB;

    $status = array();
    $componentstr = get_string('modulenameplural', 'magtest');
    // Get the relevant entries from $data and drop answers.

    foreach ($data as $key => $value) {
        if (preg_match('/^'.MAGTEST_RESETFORM_RESET."(\\d+)/", $key, $matches)) {
            $magtestid = $matches[1];
            $magtest = $DB->get_record('magtest', array('id' => $magtestid));
            $DB->delete_records('magtest_useranswer', array('magtestid' => $magtestid));
            $status[] = array('component' => $componentstr.':'.format_string($magtest->name), 'item' => get_string('resetting_data','magtest'), 'error' => false);
        }
    }
    return $status;
}

/**
 * Called by course/reset.php
 * @param $mform form passed by reference
 */
function magtest_reset_course_form_definition(&$mform) {
    global $COURSE, $DB;

    $mform->addElement('header', 'magtestheader', get_string('modulenameplural', 'magtest'));
    if (!$magtests = $DB->get_records('magtest', array('course' => $COURSE->id))) {
        return;
    }

    $mform->addElement('static', 'hint', get_string('resetting_data','magtest'));
    foreach ($magtests as $magtest) {
        if ($hasanswers = $DB->count_records('magtest_useranswer', array('magtestid' => $magtest->id))) {
            $mform->addElement('checkbox', MAGTEST_RESETFORM_RESET.$magtest->id, format_string($magtest->name));
        }
    }
}

/**
 * Print an overview of all magtests
 * for the courses.
 *
 * @param mixed $courses The list of courses to print the overview for
 * @param array $htmlarray The array of html to return
 */
function magtest_print_overview($courses, &$htmlarray) {
    global $USER, $CFG, $DB;

    $config = get_config('magtest');

    if (empty($config->showmymoodle)) {
        return; // Disabled via global config.
    }


    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$magtests = get_all_instances_in_courses('magtest', $courses)) {
        return;
    }

    $magtestids = array();

    // Check for open magtests.
    foreach ($magtests as $key => $magtest) {
        $time = time();
        $isopen = false;
        if ($magtest->endtime) {
            if ($time <= $magtest->endtime) {
                if ($magtest->starttime) {
                    if ($time >= $magtest->starttime) {
                        $isopen = true;
                    }
                } else {
                    $isopen = true;
                }
            }
        } else {
            if ($magtest->starttime) {
                if ($time >= $magtest->starttime) {
                    $isopen = true;
                }
            } else {
                $isopen = true;
            }
        }
        if ($isopen) {
            unset($magtests[$magtest->id]);
        }
    }

    $strcutoffdate = get_string('endtime', 'magtest');
    $strnotsubmittedyet = get_string('notsubmittedyet', 'magtest');
    $strsubmitted = get_string('submitted', 'magtest');
    $strmagtest = get_string('modulename', 'magtest');

    foreach ($magtests as $magtest) {

        $str = '<div class="magtest overview">';
        $str .= '<div class="name">'.$strmagtest. ': '.
               '<a '.($magtest->visible ? '':' class="dimmed"').
               'title="'.$strmagtest.'" href="'.$CFG->wwwroot.
               '/mod/magtest/view.php?id='.$magtest->coursemodule.'">'.
               format_string($magtest->name).'</a></div>';
        if ($magtest->endtime && $magtest->endtimeenable) {
            $str .= '<div class="info">'.$strcutoffdate.': '.userdate($magtest->endtime).'</div>';
        }
        $context = context_module::instance($magtest->coursemodule);
        if (has_capability('mod/magtest:viewotherresults', $context)) {

            // Count how many people need submit.
            $submissions = 0; // init

            $sql = "
                SELECT DISTINCT
                    userid, userid
                FROM
                    {magtest_useranswer}
                WHERE
                    magtestid = ?
            ";
            $answeredbyusers = $DB->get_records_sql($sql, array($magtest->id));

            if ($students = get_enrolled_users($context, 'mod/assign:view', 0, 'u.id')) {
                foreach ($students as $student) {
                    if (array_key_exists($student->id, $answeredbyusers)) {
                        $submissions++;
                    }
                }
            }

            $usersleft = count($students) - $submissions;
            if ($submissions) {
                $link = new moodle_url('/mod/magtest/view.php', array('id' => $magtest->coursemodule, 'view' => 'results'));
                $str .= '<div class="details"><a href="'.$link.'">'.get_string('userstosubmit', 'magtest', $usersleft).'</a></div>';
            }
            $str .= '</div>';
        }
        if (empty($htmlarray[$magtest->course]['magtest'])) {
            $htmlarray[$magtest->course]['magtest'] = $str;
        } else {
            $htmlarray[$magtest->course]['magtest'] .= $str;
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
    global $DB;

    $magtestid = $DB->get_field('course_modules', 'instance', array('id' => $cm->id));
    if ($DB->count_records('magtest_useranswer', array('userid' => $userid))) {
        return true;
    }
    return false;
}

function magtest_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    $fileareas = array('question', 'questionanswer');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    $itemid = (int)array_shift($args);

    if (!$magtest = $DB->get_record('magtest', array('id'=>$cm->instance))) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_magtest/$filearea/$itemid/$relativepath";

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, false); // download MUST be forced - security!
}

