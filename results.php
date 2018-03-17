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

defined('MOODLE_INTERNAL') || die();

/**
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @contributors   Etienne Roze
 * @contributors   Wafa Adham for version 2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

if (!(isset($id) and $view === 'results' and has_capability('mod/magtest:viewotherresults', $context))) {
    print 'You have not to see this page';
    exit;
}

require_once($CFG->libdir.'/tablelib.php');

$currentgroup = groups_get_course_group($course, true);
$grouptoshow = optional_param('group', $currentgroup, PARAM_INT);
$groupmode = groups_get_course_groupmode($course); // Groups are being used.
$isseparategroups = ($course->groupmode == SEPARATEGROUPS and $course->groupmodeforce && !has_capability('moodle/site:accessallgroups', $context));

if ($isseparategroups and (!$currentgroup) ) {
    echo $OUTPUT->notification('You are not in a group.');
    exit;
}

$baseurl = new moodle_url('/mod/magtest/view.php', array('id' => $cm->id, 'view' => 'results'));
groups_print_course_menu($course, $baseurl);

if (! $grouptoshow ) {
    $grouptoshow = '';
}

$users = get_users_by_capability($context, 'mod/magtest:doit', '', '', '', '', $grouptoshow);
$usersanswers = get_magtest_usersanswers($magtest->id);

if (! $usersanswers ) {
    echo $OUTPUT->notification(get_string('nouseranswer', 'magtest'));
    exit;
}

$categories = get_magtest_categories($magtest->id);
$questions = get_magtest_questions($magtest->id);
$countcat = array();

$nbtotal = 0;

foreach ($usersanswers as $useranswer) {
    if ($magtest->singlechoice) {
        // Sum each earned weight per category.
        $question = $questions[$useranswer->questionid];
        foreach ($questions->answers as $answer) {
            if ($useranswer->answerid == 1) {
                $cat = $categories[$answer->categoryid];
                $countcat[$useranswer->userid][$cat->categoryshortname] = $countcat[$useranswer->userid][$cat->categoryshortname] + $answer->weight;
            }
        }
    } else {
        // Sum earned weight in choosen category.
        $question = $questions[$useranswer->questionid];
        $answer = $question->answers[$useranswer->answerid];
        $cat = $categories[$answer->categoryid];
        $countcat[$useranswer->userid][$cat->categoryshortname] = $countcat[$useranswer->userid][$cat->categoryshortname] + $answer->weight;
    }
}

$table->head = array(get_string('users'));

foreach ($categories as $category) {
    $table->head[] = $category->categoryshortname;
    $tab_empty[$category->categoryshortname] = 0;
}

$results = array();
foreach ($users as $user) {

    $userpic = new user_picture();
    $userpic->user = $user;
    $userpic->courseid = $course->id;
    $userpic->image->src = true;

    $results[$user->id] = array_merge(
        array(
                'user' => $OUTPUT->user_picture($userpic).
                fullname($user, has_capability('moodle/site:viewfullnames', $context))
            ),
        $tab_empty,
        $countcat[$user->id]
    );
}

$table->data = $results;

echo html_writer::table($table);
