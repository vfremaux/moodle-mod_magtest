<?php
<<<<<<< HEAD

include '../../config.php';

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

// Security.
>>>>>>> MOODLE_34_STABLE
require_login();

$answerid = required_param('answerid', PARAM_INT);
$answer = $DB->get_record('magtest_answer', array('id' => $answerid));
echo $answer->helper;
<<<<<<< HEAD

?>
=======
>>>>>>> MOODLE_34_STABLE
