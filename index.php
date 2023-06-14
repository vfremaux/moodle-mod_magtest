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
 * This page lists all the instances of magtest in a particular course
 *
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @see        categories.controller.php for associated controller.
 */

// Replace magtest with the name of your module.

require('../../config.php');
require_once($CFG->dirroot.'/mod/magtest/lib.php');

$id = required_param('id', PARAM_INT);   // Course ID.

$url = new moodle_url('/mod/magtest/index.php', array('id' => $id));
$PAGE->set_url($url);

if (! $course = $DB->get_record('course', array('id' => $id))) {
    print_error('coursemisconf');
}

// Security.

require_login($course->id);

\mod_magtest\event\course_module_instance_list_viewed::create_from_course($course)->trigger();

// Get all required strings.

$strmagtests = get_string('modulenameplural', 'magtest');
$strmagtest  = get_string('modulename', 'magtest');

// Print the header.

if ($course->category) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
    $navigation = '<a href="'.$courseurl.'">'.$course->shortname.'</a> ->';
} else {
    $navigation = '';
}

$PAGE->set_title("$course->shortname: $strmagtests");
$PAGE->set_heading("$course->fullname");

echo $OUTPUT->header();

// Get all the appropriate data.

if (! $magtests = get_all_instances_in_course('magtest', $course)) {
    echo $OUTPUT->notification(get_string('nomagtests', 'magtest'), new moodle_url('/course/view.php', array('id' => $course->id)));
    echo $OUTPUT->footer();
    die;
}

// Print the list of instances (your module will probably extend this).

$timenow = time();
$strname  = get_string('name');
$strweek  = get_string('week');
$strtopic  = get_string('topic');

$table = new html_table();
if ($course->format == 'weeks') {
    $table->head = array ($strweek, $strname);
    $table->align = array ('center', 'left');
} else if ($course->format == 'topics') {
    $table->head = array ($strtopic, $strname);
    $table->align = array ('center', 'left', 'left', 'left');
} else {
    $table->head = array ($strname);
    $table->align = array ('left', 'left', 'left');
}

foreach ($magtests as $magtest) {
    $magtestname = format_string($magtest->name);
    $linkurl = new moodle_url('/mod/magtest/view.php', array('id' => $magtest->coursemodule));
    if (!$magtest->visible) {
        $class = 'dimed';
    } else {
        $class = '';
    }
    $link = '<a href="'.$linkurl.'" class="'.$class.'">'.$magtestname.'</a>';

    if ($course->format == "weeks" || $course->format == "topics") {
        $table->data[] = array ($magtest->section, $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo "<br />";

echo html_writer::table($table);

// Finish the page.

echo $OUTPUT->footer($course);

