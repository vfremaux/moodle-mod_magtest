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
 * @package    mod-magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require_once($CFG->libdir.'/tablelib.php');

if ($action) {
    $controller = 'results.controller.php';
}

if (!defined('MOODLE_INTERNAL')) {
    die('You cannot access directly to this page');
}

// Setup group state regarding the user.
$groupmode = groupmode($course, $cm);
$changegroupid = optional_param('group', -1, PARAM_INT);

if (has_capability('moodle/site:accessallgroups', $context)) {
    $groups = groups_get_all_groups($COURSE->id);
} else {
    $groups = groups_get_all_groups($COURSE->id, $USER->id);
}

$baseurl = $CFG->wwwroot."/mod/magtest/view.php?id={$cm->id}&amp;view=results";
if ($groups) {
    groups_print_course_menu($COURSE, $baseurl);
}

// Get users of the current group who can do the test.

/*
 * note that usemakegroups is not compatible with course groups as it is used to generate
 * moodle groups in a course and needs having no groups at start.
 */
if ($groupmode == NOGROUPS || $magtest->usemakegroups) {
    $users = get_users_by_capability($context, 'mod/magtest:doit', 'u.id,firstname,lastname,picture,email', 'lastname');
} else {
    $users = get_users_by_capability($context, 'mod/magtest:doit', 'u.id,firstname,lastname,picture,email', 'lastname', '', '', $currentgroupid);
}

// Do not recalculate results if they where already calculated in controller.
if (empty($categories) && $action != 'makegroups') {
    magtest_compile_results($magtest, $users, $categories, $max_cat);
}

// Make table head.

echo '<center>';
$table = new html_table();
$table->head[] = '<b>'.get_string('category', 'magtest').'</b>';
$table->head[] = '<b>'.get_string('results', 'magtest').'</b>';
$table->size = array('30%', '70%');
$table->width = '80%';

foreach($categories as $cat) {
    $symbolurl = magtest_get_symbols_baseurl($magtest).$cat->symbol;
    $symbolimg = "<img src=\"$symbolurl\" /> ";
    $scoreboard = '<table width="100%" class="magtest-user-list">';
    if (!empty($cat->users)) {
        foreach($cat->users as $userid) {
            if ($groupmode != NOGROUPS && !$magtest->usemakegroups) {
                // we ensure user is in the currently viewed group
                if (!in_array($userid, array_keys($users))) {
                    continue;
                }
            }
            $user = $DB->get_record('user', array('id' => $userid));
            $username = $OUTPUT->user_picture($user).' '.fullname($user);
            $score = @$max_cat[$user->id]->score;
            $scoreboard .= "<tr><td>{$username}</td><td align=\"right\">{$score}</td></tr>";
        }
    } else {
        $scoreboard .= '<tr><td>'.get_string('nousersinthisgroup', 'magtest').'</td></tr>';
    }
    $scoreboard .= '</table>';

    $cell = '<span class="magtest-cat-name">'.$symbolimg.' '.$cat->name.'</span><br/>'.format_string($cat->description, $cat->descriptionformat);
    $table->data[] = array($cell, $scoreboard);
}

echo html_writer::table($table);

echo '<br/>';

if ($magtest->usemakegroups) {
    $allgroups = groups_get_all_groups($COURSE->id);
    if (empty($allgroups)) {
        $params = array('id' => $cm->id, 'what' => 'makegroups');
        echo $OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/mod/magtest/view.php', $params), get_string('makegroups', 'magtest'), 'get');
    } else {
        echo $OUTPUT->box(get_string('nogroupcreationadvice', 'magtest'), 'errorbox');
    }
}
echo '</center>';
