<?php

    /**
    * Prints details stats about all answers
    * 
    * @package    mod-magtest
    * @category   mod
    * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
    * @contributors   Etienne Roze
    * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
    * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
    */

    if (!defined('MOODLE_INTERNAL')) die('You cannot use this script that way');

    if (!(isset($id) and $view === 'stat' && has_capability('mod/magtest:viewgeneralstat', $context))) {
        die('You cannot use this script that way');
     }
    require_once($CFG->libdir.'/tablelib.php');

    $groupmode = groupmode($course, $cm);
    // old mode but is the one that works !!
    $changegroupid = optional_param('group', -1, PARAM_INT);              
  //  $currentgroupid = 0 + get_and_set_current_group($course, $groupmode, $changegroupid);

    // note that usemakegroups is not compatible with course groups as it is used to generate
    // moodle groups in a course and needs having no groups at start.
    if ($groupmode == NOGROUPS || $magtest->usemakegroups){
        $users = get_users_by_capability($context, 'mod/magtest:doit', 'u.id,firstname,lastname,picture,email', 'lastname');
    } else {
        $users = get_users_by_capability($context, 'mod/magtest:doit', 'u.id,firstname,lastname,picture,email', 'lastname', '', '', $currentgroupid);
    }    
    $usersanswers = magtest_get_useranswers($magtest->id, $users);
    if (! $usersanswers ) {
        echo $OUTPUT->notification(get_string('nouseranswer','magtest'));
        return;
    }
    $categories = magtest_get_categories($magtest->id);
    $questions = magtest_get_questions($magtest->id);
    $count_cat = array();
    $nb_total = 0;
    foreach($usersanswers as $useranswer) {
        $cat = $categories[$questions[$useranswer->questionid]->answers[$useranswer->answerid]->categoryid];    
        $count_cat[$useranswer->questionid][$cat->id] = @$count_cat[$useranswer->questionid][$cat->id] + 1 ;
    }
    // compute unaswered
    $candidates = count($users);
    foreach($questions as $id => $question){
        $answered = 0;
        if (!empty($count_cat[$question->id])){
            foreach($count_cat[$question->id] as $catid => $value){
                $answered += $value;
            }
            $questions[$id]->unanswered = $candidates - $answered;
        }
    }
    $table = new html_table();
    $table->head = array(get_string('questions','magtest'));
    $table->head[] = get_string('unanswered','magtest');
    foreach($categories as $category) {
        $table->head[] = $category->name;
    }
    $results = array();
    foreach($questions as $question) {
        $data = array();
        $data[] = get_string('question','magtest').' '.$question->sortorder;
        $data[] = $questions[$question->id]->unanswered;
        foreach(array_keys($categories) as $catid){
            $data[] = @$count_cat[$question->id][$catid];
        }
        $results[] = $data;        
    }    
    echo "<center>";
    $table->data = $results;
    echo html_writer::table($table);
    echo "</center>";
?>