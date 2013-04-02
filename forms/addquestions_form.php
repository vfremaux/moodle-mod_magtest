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
       global $DB, $CFG, $cm, $qid, $id, $magtest;
        
        $mform = $this->_form;
        $mod_context = context_module::instance($id); 
        $mform->addElement('header', 'header0', get_string($this->cmd.'question', 'magtest'));
        $mform->addElement('hidden', 'qid');
        $mform->addElement('hidden', 'howmany');
        $mform->setDefault('howmany', $this->howmany);
        $mform->addElement('hidden', 'what');
        $mform->setDefault('what', 'do'. $this->cmd);
        $maxbytes = 1024 *1024 * 100 ;
        $questionoptions = array('trusttext' => true, 'subdirs' => false,'maxfiles' => 100, 'maxbytes' => $maxbytes, 'context' => $mod_context);
         
        if(!empty($qid) && $qid != -1) {
            $question = magtest_get_question($qid);
            $answers = array();
            
            foreach($question->answers as $ans) {
                $answers[$ans->categoryid] = $ans->answertext ;
            }
        }
       
        $mform->addElement('hidden', 'magtestid', $magtest->id); 
   
        if($this->cmd == 'add') {
            $mform->addElement('hidden', 'cmd', 'add');
            
            $mform->addElement('editor', 'questiontext_editor', get_string('question_text', 'magtest'), null, array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' =>  $mod_context));
            //insert hte question .
            //get the categories 
            $cats = $DB->get_records('magtest_category',array('magtestid' => $magtest->id));          
            $mform->addElement('header', 'header1', get_string('answers', 'magtest'));            
            foreach ($cats as $cat){
	            $mform->addElement('hidden', 'cats['. $cat->id.']', $cat->id); 
	            $question_answer_text = get_string('category', 'mod_magtest')." '".$cat->name."' answer";        
	            $question_editor = $mform->addElement('editor', 'questionanswer'.$cat->id.'_editor',$question_answer_text,null,array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' =>  $mod_context));   
	  			$mform->addRule('questionanswer'.$cat->id.'_editor', null, 'required', null, 'client');

	            if ($magtest->weighted){
    				$weight = 1 ;
	            	$mform->addElement('text', 'weight'.$cat->id, get_string('weight', 'magtest'), $weight);   
	            }

	            $helper_text = get_string('helpertext','mod_magtest', $cat->name);        
	            $helper_editor = $mform->addElement('editor', 'helper'.$cat->id.'_editor', $helper_text, null, array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' =>  $mod_context));   
            }
        } else if ($this->cmd == 'update') {
            $mform->addElement('hidden', 'cmd', 'update');
            $mform->addElement('hidden', 'qid', $question->id); 
            
            $questiontext_editor = $mform->addElement('editor', 'questiontext_editor', get_string('question_text', 'magtest'), null, array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' =>  $mod_context));
                            
            $question = file_prepare_standard_editor($question, 'questiontext', $questionoptions, $mod_context, 'mod_magtest', 'question', 0);                
            //insert hte question .
            //get the categories 
            $cats = $DB->get_records('magtest_category',array('magtestid' => $magtest->id));          
            $mform->addElement('header', 'header1', get_string('answers', 'magtest'));            
            foreach ($cats as $cat){        
                $mform->addElement('hidden', 'cats['. $cat->id.']', $cat->id); 
                $question_answer_text = get_string('category','mod_magtest')." '".$cat->name."' answer";
                
                //get cat answer if exists 
                $answer = $DB->get_record_select('magtest_answer', ' magtestid = ? and categoryid = ? and questionid = ? ', array($magtest->id, $cat->id, $question->id));               
                $question_editor = $mform->addElement('editor', 'questionanswer'.$cat->id.'_editor',$question_answer_text,null,array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' =>  $mod_context));                   
	  			$mform->addRule('questionanswer'.$cat->id.'_editor', null, 'required', null, 'client');

	            if ($magtest->weighted){
    				$weight = (isset($answer->weight)) ? $answer->weight : 1 ;
	            	$mform->addElement('text', 'weight'.$cat->id, get_string('weight', 'magtest'), $weight);   
	            }

	            $helper_text = get_string('helpertext','mod_magtest', $cat->name);        
	            $helper_editor = $mform->addElement('editor', 'helper'.$cat->id.'_editor', $helper_text,null,array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' =>  $mod_context));   

                if(!empty($answer)){
                	$field = 'questionanswer'.$cat->id;
                	$question->{$field} = $answer->answertext;
                	$question->{$field.'format'} = FORMAT_HTML;                
                	$question  = file_prepare_standard_editor($question,$field, $questionoptions, $mod_context,'mod_magtest', 'questionanswer', $answer->id);

                	$field = 'helper'.$cat->id;
                	$question->{$field} = $answer->helper;
                	$question->{$field.'format'} = FORMAT_HTML;
                	$question  = file_prepare_standard_editor($question, $field, $questionoptions, $mod_context, 'mod_magtest', 'helper', $answer->id);

                	$field = 'weight'.$cat->id;
                	$question->{$field} = $weight;
                }
            }
        	$this->set_data($question);
        }
        $this->add_action_buttons();
    }
}
