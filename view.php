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
require_once($CFG->dirroot.'/mod/magtest/compatlib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.
$a = optional_param('a', 0, PARAM_INT); // Magtest ID.
$view = optional_param('view', @$SESSION->view, PARAM_ACTION); // View.
$page = optional_param('page', @$SESSION->page, PARAM_ACTION); // Page.
$action = optional_param('what', '', PARAM_RAW); // Command.

// Load jquery.
$PAGE->requires->js_call_amd('mod_magtest/view', 'init');

$SESSION->view = $view;
$SESSION->page = $page;

if ($id) {
    $cm = get_coursemodule_from_id('magtest', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $magtest = $DB->get_record('magtest', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $magtest = $DB->get_record('magtest', ['id' => $a], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $magtest->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('magtest', $magtest->id, $course->id, false, MUST_EXIST);
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

// Print the main part of the page.

switch ($view) {
    case 'doit':
        if (!has_capability('mod/magtest:doit', $context)) {
            throw new moodle_exception(get_string('errornotallowed', 'magtest'));
        }
        $filetoinclude = 'maketest.php';
        break;

    case 'preview':
        if (!has_capability('mod/magtest:manage', $context)) {
            redirect ("view.php?view=doit&amp;id={$cm->id}");
        }
        $filetoinclude = 'preview.php';
        break;

    case 'categories':
        if (!has_capability('mod/magtest:manage', $context)) {
            redirect ("view.php?view=doit&amp;id={$cm->id}");
        }
        $filetoinclude = 'categories.php';
        break;

    case 'questions':
        if (!has_capability('mod/magtest:manage', $context)) {
            redirect ("view.php?view=doit&amp;id={$cm->id}");
        }
        $filetoinclude = 'questions.php';
        break;

    case 'results':
        if (!has_capability('mod/magtest:viewotherresults', $context)) {
            redirect ("view.php?view=doit&amp;id={$cm->id}");
        }
        switch ($page) {
            case 'byusers':
                $filetoinclude = 'resultsbyusers.php';
                break;

            case 'bycats':
                $filetoinclude = 'resultsbycats.php';
        }
        break;

    case 'stat':
        if (!has_capability('mod/magtest:viewgeneralstat', $context)) {
            redirect ("view.php?view=doit&amp;id={$cm->id}");
        }
        $filetoinclude = 'stat.php';
        break;

    default:;
}

// Start printing the whole view.

mod_magtest\compat::init_page($cm, $magtest);
$PAGE->set_title("$course->shortname: $magtest->name");
$PAGE->set_heading("$course->fullname");
$PAGE->navbar->add(get_string($view, 'magtest'));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);
$params = array('id' => $id);
if (!empty($view)) {
    $params['view'] = $view;
}
if (!empty($page)) {
    $params['page'] = $page;
}
if (!empty($action)) {
    $params['what'] = $action;
}
$PAGE->set_url(new moodle_url('/mod/magtest/view.php', $params));

echo $OUTPUT->header();

echo mod_magtest\compat::legacy_nav($cm, $context, $view, $page);

require($CFG->dirroot.'/mod/magtest/'.$filetoinclude);

// Finish the page.

echo '<br/>';
echo $OUTPUT->footer($course);
