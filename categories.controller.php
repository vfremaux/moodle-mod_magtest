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
 * Controller for "categories" list
 * Keep category global use case and defer add and update to moodle form sidepath
 *
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @see        categories.php for view.
 * @usecase    deletecategory
 * @usecase    raisecategory
 * @usecase    lowercategory
 */
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/mod/magtest/listlib.php';

// Delete a category *************************************************************************.

if ($action == 'deletecategory') {
    $catid = required_param('catid', PARAM_INT);

    $answers = $DB->get_records('magtest_answer', array('categoryid' => $catid), '', 'id,id');
    if (!empty($answers)) {
        $DB->delete_records('magtest_answer', array('categoryid' => $catid));
        list($insql, $inparams) = $DB->get_in_or_equal($deletedanswerslist);
        $DB->delete_records_select('magtest_useranswer', "answerid $insql", $inparams);
    }
    magtest_list_delete($catid, 'magtest_category');
}

// Raises a category ********************************************************************.

if ($action == 'raisecategory') {
    $catid = required_param('catid', PARAM_INT);
    magtest_list_up($magtest, $catid, 'magtest_category');
}

// Lower a category *********************************************************************.

if ($action == 'lowercategory') {
    $catid = required_param('catid', PARAM_INT);
    magtest_list_down($magtest, $catid, 'magtest_category');
}
