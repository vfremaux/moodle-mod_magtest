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
 * This view allows checking deck states
 *
 * @package mod-magtest
 * @category mod
 * @author Valery Fremaux
 * @contributors Etienne Roze
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
* overrides moodleform for test setup
*/
class mod_magtest_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $COURSE;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        $mform->setType('name', PARAM_CLEANHTML);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor(true, get_string('intro', 'magtest'));

        $startdatearray[] = &$mform->createElement('date_time_selector', 'starttime', '');
        $startdatearray[] = &$mform->createElement('checkbox', 'starttimeenable', '');
        $mform->addGroup($startdatearray, 'startfrom', get_string('starttime', 'magtest'), ' ', false);
        $mform->disabledIf('startfrom', 'starttimeenable');

        $enddatearray[] = &$mform->createElement('date_time_selector', 'endtime', '');
        $enddatearray[] = &$mform->createElement('checkbox', 'endtimeenable', '');
        $mform->addGroup($enddatearray, 'endfrom', get_string('endtime', 'magtest'), ' ', false);
        $mform->disabledIf('endfrom', 'endtimeenable');

        $mform->addElement('checkbox', 'singlechoice', get_string('singlechoice', 'magtest'));
        $mform->addHelpButton('singlechoice', 'singlechoice', 'magtest');

        $mform->addElement('checkbox', 'weighted', get_string('weighted', 'magtest'));
        $mform->addHelpButton('weighted', 'weighted', 'magtest');
        $mform->disabledIf('weight', 'singlechoice', 'checked');

        $mform->addElement('checkbox', 'usemakegroups', get_string('usemakegroups', 'magtest'));
        $mform->addHelpButton('usemakegroups', 'usemakegroups', 'magtest');

        $mform->addElement('text', 'pagesize', get_string('pagesize', 'magtest'), array('size' => 3));
        $mform->addHelpButton('pagesize', 'pagesize', 'magtest');
        $mform->setType('pagesize', PARAM_TEXT);

        $mform->addElement('checkbox', 'allowreplay', get_string('allowreplay', 'magtest'));
        $mform->addHelpButton('allowreplay', 'pagesize', 'magtest');

        $mform->addElement('htmleditor', 'result', get_string('resulttext', 'magtest'));
        $mform->setType('result', PARAM_RAW);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    public function validation($data, $files = null) {
        $errors = array();
        return $errors;
    }
}
