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
 * Prints results of the test for the user
 *
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

if ($action) {
    $controller = 'results.controller.php';
}

// Setup group state regarding the user.

$groupmode = groups_get_activity_groupmode($cm);
$changegroupid = optional_param('group', -1, PARAM_INT);

if (has_capability('moodle/site:accessallgroups', $context)) {
    $groups = groups_get_all_groups($COURSE->id);
} else {
    $groups = groups_get_all_groups($COURSE->id, $USER->id);
}
$baseurl = new moodle_url('/mod/magtest/view.php', array('id' => $cm->id, 'view' => 'results'));

if ($groups) {
    groups_print_course_menu($COURSE, $baseurl);
}

// Get users of the current group who can do the test.

/*
 * Note that usemakegroups is not compatible with course groups as it is used to generate
 * moodle groups in a course and needs having no groups at start.
 */
$fields = 'u.id,picture,email,imagealt,'.get_all_user_name_fields(true, 'u');
if ($groupmode == NOGROUPS || $magtest->usemakegroups) {
    $users = get_users_by_capability($context, 'mod/magtest:doit', $fields, 'lastname');
} else {
    $users = get_users_by_capability($context, 'mod/magtest:doit', $fields, 'lastname', '', '', $currentgroupid);
}

// Get missing users.

if (!$missings = magtest_get_unsubmitted_users($magtest, $users)) {
    $missings = array();
}

$usersanswers = magtest_get_useranswers($magtest->id, $users);
if (! $usersanswers ) {
    echo $OUTPUT->notification(get_string('nouseranswer', 'magtest'));
    return;
}

$categories = magtest_get_categories($magtest->id);
$questions = magtest_get_questions($magtest->id);
$countcat = array();
$nbtotal = 0;

foreach ($usersanswers as $useranswer) {
    if ($magtest->singlechoice) {
        $question = $questions[$useranswer->questionid];
        foreach ($question->answers as $answer) {
            // Aggregate scores for each cat on each user.
            $cat = $categories[$answer->categoryid];
            $countcat[$useranswer->userid][$cat->id] = 0 + @$countcat[$useranswer->userid][$cat->id] + $answer->weight;
        }
    } else {
        $question = $questions[$useranswer->questionid];
        $answer = $question->answers[$useranswer->answerid];
        $cat = $categories[$answer->categoryid];
        // Aggregate scores.
        $countcat[$useranswer->userid][$cat->id] = 0 + @$countcat[$useranswer->userid][$cat->id] + $answer->weight;
    }
}

// Get max for each user.

foreach ($users as $user) {
    if (array_key_exists($user->id, $missings)) {
        continue;
    }
    $maxcat[$user->id] = new StdClass();
    $maxcat[$user->id]->score = 0;
    $maxcat[$user->id]->catid = 0;
    foreach ($categories as $cat) {
        if (@$countcat[$user->id][$cat->id] > $maxcat[$user->id]->score) {
            $maxcat[$user->id]->score = $countcat[$user->id][$cat->id];
            $maxcat[$user->id]->catid = $cat->id;
        }
    }
}

// Make table head.

echo '<center>';
$table = new html_table();
$table->head[] = '<b>'.get_string('users').'</b>';
$table->head[] = '<b>'.get_string('results', 'magtest').'</b>';
$table->size = array('30%', '70%');
$table->width = '80%';
foreach ($users as $userid => $user) {
    if (array_key_exists($user->id, $missings)) {
        continue;
    }
    $userlink = "<a href=\"{$CFG->wwwroot}/user/view.php?id={$userid}\">".fullname($user).'</a>';
    $username = $OUTPUT->user_picture($user).' '.$userlink;
    $scoreboard = '<table width="100%" class=\"magtest-scoretable\">';
    foreach ($categories as $category) {
        if ($maxcat[$user->id]->catid == $category->id) {
            $pf = '<span class="winner">';
            $sf = '</span>';
        } else {
            $pf = '';
            $sf = '';
        }
        $score = @$countcat[$user->id][$category->id];
        $symbolurl = magtest_get_symbols_baseurl($magtest).$category->symbol;
        $symbolimg = "<img src=\"$symbolurl\" /> ";
        $scoreboard .= "<tr><td>{$pf}{$symbolimg} {$category->name}{$sf}</td><td align=\"right\">{$pf}{$score}{$sf}</td></tr>";
    }
    $scoreboard .= '</table>';

    $table->data[] = array($username, $scoreboard);
}

echo html_writer::table($table);

unset($table);

if (!empty($missings)) {
    echo $OUTPUT->heading(get_string('unanswered', 'magtest'));
    $table = new html_table();
    $table->head[] = '<b>'.get_string('users').'</b>';
    $table->align = array('left');
    $table->size = array('100%');
    $table->width = '80%';
    foreach ($missings as $userid => $user) {
        $userurl = new moodle_url('/user/view.php', array('id' => $userid));
        $userlink = '<a href="'.$userurl.'">'.fullname($user).'</a>';
        $username = $OUTPUT->user_picture($user).' '.$userlink;
        $table->data[] = array($username);
    }
    echo html_writer::table($table);
}
echo '</center>';
