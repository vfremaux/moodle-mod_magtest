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
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @see        categories.controller.php for associated controller.
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/magtest/forms/addcategories_form.php');
require_once($CFG->dirroot.'/mod/magtest/classes/magtest.class.php');

$id = required_param('id', PARAM_INT);
$catid = required_param('catid', PARAM_INT);
$howmany = optional_param('howmany', 1, PARAM_INT);

if ($id) {
    if (! $cm = get_coursemodule_from_id('magtest', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $cm = $DB->get_record('course_modules', array('id' => $id))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('coursemisconf');
    }
    if (! $magtest = $DB->get_record('magtest', array('id' => $cm->instance))) {
        print_error('invalidcoursemodule');
    }
}

// Security.

require_course_login($course->id, true, $cm);

$url = new moodle_url('/mod/magtest/editcategories.php', array('id' => $id));
$editurl = new moodle_url('/mod/magtest/view.php', array('id' => $id, 'view' => 'categories'));

if ($catid <= 0) {
    $form = new Category_Form($magtest, 'add', $howmany, $url);
} else {
    $form = new Category_Form($magtest, 'update', $howmany, $url);
}

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/magtest/view.php', array('id' => $id)));
}

$PAGE->set_title("$course->shortname: $magtest->name");
$PAGE->set_heading("$course->fullname");
$PAGE->navbar->add(get_string('categories', 'magtest'), $editurl);
$PAGE->navbar->add(get_string('addcategory', 'magtest'));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);
$PAGE->set_url($url);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'magtest'));

if ($form->is_cancelled()) {
    redirect($editurl);
}

if ($data = $form->get_data()) {
    $cmd = $data->cmd;

    if ($cmd == 'add') {

        $count = $data->howmany;

        for ($i = 1 ; $i <= $count ; $i++) {

            $cat = new StdClass();

            $var = 'catname_'.$i;
            $cat->name = $data->{$var};

            if ($cat->name == '' || empty($cat->name)) {
                // Unfilled category, ignore it.
                continue;
            }

            $var = 'catsymbol_'.$i;
            $cat->symbol = $data->{$var};

            $var = 'catdescription_'.$i;
            $cat->description = $data->{$var}['text'];

            $var = 'catdescriptionformat_'.$i;
            $cat->descriptionformat = 1;

            $var = 'catresult_'.$i;
            $cat->result = $data->{$var}['text'];

            $var = 'outputgroupname_'.$i;
            $cat->outputgroupname = @$data->{$var};

            $var = 'outputgroupdesc_'.$i;
            $cat->outputgroupdesc = @$data->{$var};

            $catid = magtest::addCategory($magtest->id, $cat);

            if (!$catid) {
                 print_error('erroraddcategory', 'magtest', $editurl);
            }
        }
    } else {
        // Update category.
        $category = $DB->get_record('magtest_category', array('id' => $catid));
        $category->name = $data->catname;
        $category->symbol = $data->symbol;
        $category->description = $data->catdescription['text'];
        $category->result = $data->catresult['text'];

        if ($magtest->usemakegroups) {
            $category->outputgroupname = $data->outputgroupname;
            $category->outputgroupdesc = $data->outputgroupdesc;
        }

        $DB->update_record('magtest_category', $category);
    }

    redirect($editurl);
    die();
}
 
if ($catid >= 0) {
   $category = $DB->get_record('magtest_category', array('id' => $catid));
   $form->set_data($category);
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer($course);
