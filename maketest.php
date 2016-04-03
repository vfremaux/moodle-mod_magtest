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
 * Allows answering to the test, question by question
 *
 * @package    mod-magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @see        preview.controller.php for associated controller.
 */

if (!defined('MOODLE_INTERNAL')) {
    die('You cannot access directly to this page');
}

$renderer = $PAGE->get_renderer('mod_magtest');

if ($magtest->starttimeenable && time() <= $magtest->starttime) {
    echo '<center>';
    echo $OUTPUT->box(get_string('notopened', 'magtest'), 'errorbox');
    echo '</center>';
    return;
}

if (!magtest_test_configuration($magtest)) {
    echo '<center>';
    echo $OUTPUT->box(get_string('testnotallok', 'magtest'));
    echo $OUTPUT->continue_button($CFG->wwwroot.'/course/view.php?id='.$course->id);
    echo '</center>';
    return;
}

$replay = optional_param('replay', 0, PARAM_BOOL);
$currentpage = optional_param('qpage', 0, PARAM_INT);

if ($replay && $magtest->allowreplay) {
    $DB->delete_records('magtest_useranswer', array('magtestid' => $magtest->id, 'userid' => $USER->id));
}

// Run controller.

$nextset = magtest_get_next_questionset($magtest, $currentpage);

if ($magtest->pagesize) {
    $donerecords = $DB->count_records_select('magtest_useranswer', "magtestid = $magtest->id AND userid = $USER->id ");
    $allpages = ceil(($DB->count_records('magtest_question', array('magtestid' => $magtest->id)) / $magtest->pagesize));
} else {
    $allpages = 1; // one unique page of any length
}

if (!$nextset) {
    echo $OUTPUT->notification(get_string('testfinish','magtest'));
    include($CFG->dirroot.'/mod/magtest/testfinished.php');
    return;
}

// Run controller.

if ($action) {
    require($CFG->dirroot.'/mod/magtest/maketest.controller.php');
}

// Keep this after test finished test, to allow students that have 
// completed the test to see results.
if ($magtest->endtimeenable && time() >= $magtest->endtime) {
    echo '<center>';
    echo $OUTPUT->box(get_string('closed', 'magtest'), 'errorbox');
    echo '</center>';
    return;
}
$categories = magtest_get_categories($magtest->id);

echo $OUTPUT->heading(get_string('answerquestions', 'magtest').format_string($magtest->name).' : '.($currentpage + 1).'/'.$allpages);

// print a description on first page.
if (!empty($magtest->description) && $currentpage == 1) {
    echo '<br/>';
    echo $OUTPUT->box(format_string($magtest->description));
}

echo $renderer->make_test($magtest, $cm, $context, $nextset, $categories);

