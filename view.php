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
 * This page prints most views of a magtest instance
 *
 * @author Valery Frmeaux (valery.fremaux@gmail.com), Etienne Roze
 * @version $Id: view.php,v 1.4 2012-11-01 21:12:55 vf Exp $
 * @package magtest
 */

require('../../config.php');
require_once($CFG->dirroot.'/mod/magtest/lib.php');
require_once($CFG->dirroot.'/mod/magtest/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a = optional_param('a', 0, PARAM_INT); // Magtest ID
$view = optional_param('view',@$SESSION->view, PARAM_ACTION); // View.
$page = optional_param('page',@$SESSION->page, PARAM_ACTION); // Page.
$action = optional_param('what', '', PARAM_RAW); // Command.

// Load jquery.
$PAGE->requires->js('/mod/magtest/js/view.js');

$SESSION->view = $view;
$SESSION->page = $page;

if ($id) {
    if (!$cm = get_coursemodule_from_id('magtest', $id)) {
        print_error ('invalidcoursemodule');
    }

    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error ('coursemisconf');
    }

    if (!$magtest = $DB->get_record('magtest', array('id' => $cm->instance))) {
        print_error ('invalidcoursemodule');
    }
} else {
    if (!$magtest = $DB->get_record('magtest', array('id' => $a))) {
        print_error ('invalidcoursemodule');
    }

    if (!$course = $DB->get_record('course', array('id' => $magtest->course))) {
        print_error ('coursemisconf');
    }

    if (!$cm = get_coursemodule_from_instance('magtest', $magtest->id, $course->id)) {
        print_error ('invalidcoursemodule');
    }
}

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Trigger module viewed event.
$eventparams = array(
    'objectid' => $magtest->id,
    'context' => $context,
);

$event = \mod_magtest\event\course_module_viewed::create($eventparams);
$event->add_record_snapshot('magtest', $magtest);
$event->trigger();

// Print the page header.

$strmagtests = get_string('modulenameplural', 'magtest');
$strmagtest  = get_string('modulename', 'magtest');

// Guest trap.

if (isguestuser()) {
    print_error('guestcannotuse', 'magtest', '', $CFG->wwwroot.'/course/view.php?id='.$course->id);
    exit;
}

// Print tabs.

if (!preg_match("/doit|preview|categories|questions|results|stat/", $view)) {
    if (has_capability('mod/magtest:manage', $context)) {
        $view = 'preview';
    } else {
        $view = 'doit';
    }
}

if (has_capability('mod/magtest:doit', $context)) {
    $tabname = get_string('doit', 'magtest');
    $row[] = new tabobject('doit', "view.php?id={$cm->id}&amp;view=doit", $tabname);
}

if (has_capability('mod/magtest:manage', $context)) {
    $tabname = get_string('preview', 'magtest');
    $row[] = new tabobject('preview', "view.php?id={$cm->id}&amp;view=preview", $tabname);
    $tabname = get_string('categories', 'magtest');
    $row[] = new tabobject('categories', "view.php?id={$cm->id}&amp;view=categories", $tabname);
    $tabname = get_string('questions', 'magtest');
    $row[] = new tabobject('questions', "view.php?id={$cm->id}&amp;view=questions", $tabname);
    $tabname = get_string('import', 'magtest');
    $row[] = new tabobject('import', $CFG->wwwroot."/mod/magtest/import/import_questions.php?id={$cm->id}", $tabname);
}

if (has_capability('mod/magtest:viewotherresults', $context)) {
    $tabname = get_string('results', 'magtest');
    $row[]   = new tabobject('results', "view.php?id={$cm->id}&amp;view=results", $tabname);
}

if (has_capability('mod/magtest:viewgeneralstat', $context)) {
    $tabname = get_string('stat', 'magtest');
    $row[]   = new tabobject('stat', "view.php?id={$cm->id}&amp;view=stat", $tabname);
}

$tabrows[] = $row;

if ($view == 'results') {
    if (!preg_match("/byusers|bycats/", $page)) {
        $page = 'bycats';
    }

    $tabname = get_string('resultsbyusers', 'magtest');
    $tabrows[1][]= new tabobject('byusers', "view.php?id={$cm->id}&amp;view=results&amp;page=byusers", $tabname);
    $tabname = get_string('resultsbycats', 'magtest');
    $tabrows[1][]= new tabobject('bycats', "view.php?id={$cm->id}&amp;view=results&amp;page=bycats", $tabname);

    if (!empty($page)) {
        $selected = $page;
        $activated = array($view);
    }
} else {
    $selected = $view;
    $activated = '';
}

// Print the main part of the page.

switch ($view) {
    case 'doit':
        if (!has_capability('mod/magtest:doit', $context)) {
            print_error('errornotallowed', 'magtest');
        }
        $file_to_include = 'maketest.php';
        break;

    case 'preview':
        if (!has_capability('mod/magtest:manage', $context)) {
            redirect ("view.php?view=doit&amp;id={$cm->id}");
        }
        $file_to_include = 'preview.php';
        break;

    case 'categories':
        if (!has_capability('mod/magtest:manage', $context)) {
            redirect ("view.php?view=doit&amp;id={$cm->id}");
        }
        $file_to_include = 'categories.php';
        break;

    case 'questions':
        if (!has_capability('mod/magtest:manage', $context)) {
            redirect ("view.php?view=doit&amp;id={$cm->id}");
        }
        $file_to_include = 'questions.php';
        break;

    case 'results':
        if (!has_capability('mod/magtest:viewotherresults', $context)) {
            redirect ("view.php?view=doit&amp;id={$cm->id}");
        }
        switch ($page) {
            case 'byusers':
                $file_to_include = 'resultsbyusers.php';
                break;

            case 'bycats':
                $file_to_include = 'resultsbycats.php';
        }
        break;

    case 'stat':
        if (!has_capability('mod/magtest:viewgeneralstat', $context)) {
            redirect ("view.php?view=doit&amp;id={$cm->id}");
        }
        $file_to_include = 'stat.php';
        break;

    default:;
}

// Start printing the whole view.

$PAGE->set_title("$course->shortname: $magtest->name");
$PAGE->set_heading("$course->fullname");
$PAGE->navbar->add(get_string($view, 'magtest'));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);
$PAGE->set_url($CFG->wwwroot . '/mod/magtest/view.php?id=' . $id);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'magtest'));

echo $OUTPUT->header();

echo $OUTPUT->container_start('mod-header');
print_tabs($tabrows, $selected, '', $activated);
echo '<br/>';
echo $OUTPUT->container_end();

include $CFG->dirroot.'/mod/magtest/'.$file_to_include;

// Finish the page.

echo '<br/>';
echo $OUTPUT->footer($course);
