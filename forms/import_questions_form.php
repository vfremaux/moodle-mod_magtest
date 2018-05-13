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
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class ImportQuestionsForm extends moodleform {

    public function definition() {
        global $COURSE;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $maxbytes = $COURSE->maxbytes;

        $fileoptions = array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1);

        $mform->addElement('filepicker', 'inputs', get_string('importfile', 'magtest'), $fileoptions);

        $label = get_string('clearalldata', 'magtest');
        $mform->addElement('checkbox', 'clearalldata', $label, get_string('clearalladvice', 'magtest'));

        $this->add_action_buttons();
    }
}