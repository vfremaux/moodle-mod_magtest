<?php

/** 
* This view allows checking deck states
* 
* @package mod-magtest
* @category mod
* @author Valery Fremaux
* @contributors Etienne Roze
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
*/

/**
* Requires and includes 
*/

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

/**
* overrides moodleform for test setup
*/
class mod_magtest_mod_form extends moodleform_mod {

	function definition() {

	  global $CFG, $COURSE;
	  $mform    =& $this->_form;
	  
	  //-------------------------------------------------------------------------------
	  $mform->addElement('header', 'general', get_string('general', 'form'));
	  
	  $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
	  $mform->setType('name', PARAM_CLEANHTML);
	  $mform->addRule('name', null, 'required', null, 'client');
	  
	  $mform->addElement('htmleditor', 'description', get_string('description'));
	  $mform->setType('description', PARAM_RAW);
	  $mform->setHelpButton('description', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');
	  // $mform->addRule('summary', get_string('required'), 'required', null, 'client');
	  
	  $startdatearray[] = &$mform->createElement('date_time_selector', 'starttime', '');
	  $startdatearray[] = &$mform->createElement('checkbox', 'starttimeenable', '');
	  $mform->addGroup($startdatearray, 'startfrom', get_string('starttime', 'magtest'), ' ', false);
	  $mform->disabledIf('startfrom', 'starttimeenable');
	
	  $enddatearray[] = &$mform->createElement('date_time_selector', 'endtime', '');
	  $enddatearray[] = &$mform->createElement('checkbox', 'endtimeenable', '');
	  $mform->addGroup($enddatearray, 'endfrom', get_string('endtime', 'magtest'), ' ', false);
	  $mform->disabledIf('endfrom', 'endtimeenable');
	  
	  $mform->addElement('checkbox', 'weighted', get_string('weighted', 'magtest'));
	  $mform->setHelpButton('weighted', array('weighted', get_string('weighted', 'magtest'), 'magtest'));

	  $mform->addElement('checkbox', 'usemakegroups', get_string('usemakegroups', 'magtest'));
	  $mform->setHelpButton('usemakegroups', array('usemakegroups', get_string('usemakegroups', 'magtest'), 'magtest'));

	  $mform->addElement('text', 'pagesize', get_string('pagesize', 'magtest'), array('size' => 3));
	  $mform->setHelpButton('pagesize', array('pagesize', get_string('pagesize', 'magtest'), 'magtest'));

	  $mform->addElement('checkbox', 'allowreplay', get_string('allowreplay', 'magtest'));
	  $mform->setHelpButton('allowreplay', array('pagesize', get_string('allowreplay', 'magtest'), 'magtest'));
	  
	  $mform->addElement('htmleditor', 'result', get_string('resulttext', 'magtest'));
	  $mform->setType('result', PARAM_RAW);
	  $mform->setHelpButton('result', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');

	  $this->standard_coursemodule_elements();
	  
	  $this->add_action_buttons();
	}

    	/*
	function definition_after_data(){
	  $mform    =& $this->_form;
	 
	  }*/
	
	function validation($data) {
	    $errors = array();
	    
	    return $errors;
	}

}
?>