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

        $mform->addElement('checkbox', 'clearalldata', get_string('clearalldata', 'magtest'), get_string('clearalladvice', 'magtest'));

        $this->add_action_buttons();

    }

    public function validation($data, $files = null) {
        return false;
    }
}