<?php

/**
* Controller for "maketest"
* 
* @package    mod-magtest
* @category   mod
* @author     Valery Fremaux <valery.fremaux@club-internet.fr>
* @contributors   Etienne Roze
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
* @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
* @see        resultsbycats.php for view.
 *
* @usecase    makegroups
*/

if (!defined('MOODLE_INTERNAL')) {
    die('You cannot access directly to this page');
}

/********************************** make moodle groups from results ***********************/
if ($action == 'makegroups'){
    $groupmode = groupmode($course, $cm);
    if ($groupmode == NOGROUPS || $magtest->usemakegroups){
        $users = get_users_by_capability($context, 'mod/magtest:doit', 'u.id,firstname,lastname,picture,email', 'lastname');
    } else {
        print_error('errorbadgroupmode', 'magtest');
    }

    magtest_compile_results($magtest, $users, $categories, $max_cat);

    foreach($categories as $category){
        $now = time();
        $group = new StdClass;
        $group->courseid = $course->id;
        $group->name = (empty($category->outputgroupname)) ? $category->name : $category->outputgroupname ;
        $group->description = $category->outputgroupdesc;
        $group->enrolmentkey = '';
        $group->timecreated = $now;
        $group->timemodified = $now;
        print_object($group);
        if (!$groupid = $DB->insert_record('groups', addslashes_recursive($group))){
            print_error('errorcreatinggroup', 'magtest');
        }
        if (!empty($category->users)){
            foreach($category->users as $userid){
                $groupmember = new StdClass;
                $groupmember->groupid = $groupid;
                $groupmember->userid = $userid;
                $groupmember->timeadded = $now;
                if (!$DB->insert_record('groups_members', $groupmember)){
                    print_error('errorgroupmembership', 'magtest');
                }
            }
        }
    }
    redirect($CFG->wwwroot.'/group/index.php?id='.$course->id);
}

?>