<?php
<<<<<<< HEAD

/**
* Controller for question management
* @package mod-magtest
* @category mod
* @author Valéry Frémaux, Etienne Roze
*
* @usecase up
* @usecase down
* @usecase delete
*/

if (!defined('MOODLE_INTERNAL')) {
    die('You cannot access directly to this page');
}

/**
* Requires and includes
*/
include_once $CFG->dirroot."/mod/magtest/listlib.php";

/******************************* Delete a question ****************/
if ($action == 'delete'){
=======
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
 * Controller for question management
 * @package mod-magtest
 * @category mod
 * @author Valéry Frémaux, Etienne Roze
 *
 * @usecase up
 * @usecase down
 * @usecase delete
 */

require_once($CFG->dirroot.'/mod/magtest/listlib.php');

/* ****************************** Delete a question *************** */

if ($action == 'delete') {
>>>>>>> MOODLE_34_STABLE
    $qid = required_param('qid', PARAM_INT);

    $DB->delete_records('magtest_useranswer', array('questionid' => $qid));
    $DB->delete_records('magtest_answer', array('questionid' => $qid));
    magtest_list_delete($qid, 'magtest_question');
}
<<<<<<< HEAD
/******************************* Raises a question in the list ****************/
else if ($action == 'up'){
=======

/* ****************************** Raises a question in the list *************** */

if ($action == 'up') {
>>>>>>> MOODLE_34_STABLE
    $qid = required_param('qid', PARAM_INT);

    magtest_list_up($magtest, $qid, 'magtest_question');
}
<<<<<<< HEAD
/******************************* Lowers a question in the list ****************/
else if ($action == 'down'){
=======

/* ****************************** Lowers a question in the list *************** */

if ($action == 'down') {
>>>>>>> MOODLE_34_STABLE
    $qid = required_param('qid', PARAM_INT);

    magtest_list_down($magtest, $qid, 'magtest_question');
}
