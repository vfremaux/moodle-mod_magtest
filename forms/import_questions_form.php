<?php

require_once $CFG->libdir.'/formslib.php';

class ImportQuestionsForm extends moodleform{
	
	function definition(){
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
	
	function validation($data, $files = null){
		
		return false;
	}
}