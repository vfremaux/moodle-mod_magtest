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

if ($groupmode == NOGROUPS || $magtest->usemakegroups) {
    $users = get_users_by_capability($context, 'mod/magtest:doit', 'u.id,firstname,lastname,picture,email', 'lastname');
} else {
    $users = get_users_by_capability($context, 'mod/magtest:doit', 'u.id,firstname,lastname,picture,email', 'lastname', '', '', $currentgroupid);
}

$usersanswers = magtest_get_useranswers($magtest->id, $users);

if (! $usersanswers ) {
    echo $OUTPUT->notification(get_string('nouseranswer', 'magtest'));
    return;
}

$categories = magtest_get_categories($magtest->id);
$questions = magtest_get_questions($magtest->id);
$count_cat = array();
$nb_total = 0;
foreach ($usersanswers as $useranswer) {
	if (!$magtest->singlechoice) {
		$question = $questions[$useranswer->questionid];
		// sum each earned weight per category
		foreach ($question->answers as $answer) {
			$cat = $categories[$answer->categoryid];
			// count cat use
  			$count_cat[$useranswer->questionid][$cat->id] = @$count_cat[$useranswer->questionid][$cat->id] + 1;
		}
        $count_users[$useranswer->userid] = 1;
    } else {
    	// counts how many hits on this question 
		$question = $questions[$useranswer->questionid];
		if ($useranswer->answerid == 1) {
			$count_cat[$useranswer->questionid] = @$count_cat[$useranswer->questionid] + 1;
		}
        $count_users[$useranswer->userid] = 1;
    }
}

// compute unanswered

$candidates = count($users);
foreach ($questions as $id => $question) {
	if ($magtest->singlechoice) {
		$questions[$id]->unanswered = $candidates - $count_cat[$question->id];
	} else {
        $answered = 0;
        if (!empty($count_cat[$question->id])){
            foreach ($count_cat[$question->id] as $catid => $value) {
                $answered += $value;
            }
            $questions[$id]->unanswered = $candidates - $answered;
        }
    }
}

$neveranswered = $candidates - count(array_keys($count_users));

$table = new html_table();
$table->width = '90%';
$table->head = array('', get_string('questions', 'magtest'), get_string('unanswered', 'magtest'));

if ($magtest->singlechoice){
    $table->head[] = get_string('selections', 'magtest');
    foreach ($questions as $question) {
        $data = array();
        $data[] = $question->sortorder.'.';
		$data[] = format_text($question->questiontext, $question->questiontextformat);
        $data[] = $questions[$question->id]->unanswered;
        $data[] = 0 + @$count_cat[$question->id];
        $table->data[] = $data;
    }
} else {
    foreach ($categories as $category) {
        $table->head[] = $category->name;
    }

    foreach ($questions as $question) {
        $data = array();
        $data[] = $question->sortorder.'.';
		$data[] = format_text($question->questiontext, $question->questiontextformat);
        $data[] = $questions[$question->id]->unanswered;
        foreach (array_keys($categories) as $catid) {
            $data[] = @$count_cat[$question->id][$catid];
        }
        $table->data[] = $data;        
    }
}

echo '<center>';

echo '<b>'.get_string('noanswerusers', 'magtest').':</b> '.$neveranswered;

echo html_writer::table($table);
echo '</center>';
