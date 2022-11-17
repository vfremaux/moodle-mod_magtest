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
 * @package     mod_magtest
 * @category    mod
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @contributors   Etienne Roze
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright   (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @see         categories.controller.php for associated controller.
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/magtest/forms/categories_form.php');

$categoriesform = new magtest_categories_form($magtest);
$categoriesform->set_data(array('id' => $cm->id));

if ($categoriesform->is_cancelled()) {
    redirect (new moodle_url('/mod/magtest/view.php', array('id' => $cm->id)));
}

$categoriesform->display();
