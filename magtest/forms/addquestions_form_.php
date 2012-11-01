<?php

include_once $CFG->libdir.'/formslib.php';
include_once $CFG->dirroot.'/mod/magtest/locallib.php';

/**
* Form to add or update categories
*
*/

class Question_Form extends moodleform{
	
	var $cmd;
	var $magtest;
	var $howmany;
	
	function __construct(&$magtest, $cmd, $howmany, $action){
		$this->cmd = $cmd;
		$this->magtest = $magtest;
		$this->howmany = $howmany;
		parent::__construct($action);
	}
	
	function definition(){
		global $DB,$CFG,$cm,$qid,$id,$magtest;
	
        if($qid>0)
        {
              $categories = magtest_get_questions($this->magtest->id);
        }
        
		$mform = $this->_form;
   
		$mform->addElement('header', 'header0', get_string($this->cmd.'question', 'magtest'));
		$mform->addElement('hidden', 'id');
        $mform->setDefault('id', $cm->id);
        $mform->addElement('hidden', 'view');
        $mform->setDefault('view','question');
		
        if(!empty($question))
        {
        $mform->addElement('hidden', 'question[id]');
        $mform->setDefault('question[id]', $question->id);
        $mform->addElement('hidden', 'question[qorder]');
        $mform->setDefault('question[qorder]', $question->sortorder); 
        }
        
        $mform->addElement('editor', 'questiontext',get_string('question_text','magtest'));
        //insert hte question .

        
        //get the categories 
        $cats = $DB->get_records('magtest_category',array('magtestid'=>$magtest->id));
        
        $mform->addElement('header', 'header1', get_string('answers', 'magtest'));
        
        foreach ($cats as $cat)
        {
        $mform->addElement('hidden','cats['.$cat->id.']');    
        $mform->setDefault('cats['.$cat->id.']', $cat->id);
        
        $question_editor = $mform->addElement('editor', 'questioneanswer['.$cat->id.']', get_string('description'),null,array('maxfiles' => EDITOR_UNLIMITED_FILES,
                        'noclean' => true, 'context' =>  $mod_context));
        $question_editor->setValue(array('text'=>$category->description));
        
        //print the question answers 
      //  $mform->addElement('textarea', 'questionanswer['.$cat->id.']',get_string('answer','magtest')." for category:".$cat->name,array('cols'=>80,'rows'=>3));
       // $mform->addElement('text', 'weight_'.$cat->id,get_string('weight','magtest'));
      //  $mform->addElement('editor', 'helpertext['.$cat->id.']',get_string('helpertext','magtest'));
        }
        
        
         
        
        $this->add_action_buttons();
	}
}
