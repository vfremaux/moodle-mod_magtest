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
        
        $mform = $this->_form;
        $mod_context = get_context_instance(CONTEXT_MODULE,$id); 
        $mform->addElement('header', 'header0', get_string($this->cmd.'question', 'magtest'));
        $mform->addElement('hidden', 'qid');
        $mform->addElement('hidden', 'howmany');
        $mform->setDefault('howmany', $this->howmany);
        $mform->addElement('hidden', 'what');
        $mform->setDefault('what', 'do'.$this->cmd);
        
        if(!empty($qid) && $qid != -1)
        {
            $question = magtest_get_question($qid);
            $answers=array();
            
            foreach($question->answers as $ans)
            {
                $answers[$ans->categoryid]= $ans->answertext ;
            }
        }
       
        $mform->addElement('hidden', 'magtestid', $magtest->id); 
   
        if($this->cmd == 'add')
        {
            $mform->addElement('hidden', 'cmd', 'add');
            
            $mform->addElement('editor', 'questiontext',get_string('question_text','magtest'));
            //insert hte question .
            //get the categories 
            $cats = $DB->get_records('magtest_category',array('magtestid'=>$magtest->id));          
            $mform->addElement('header', 'header1', get_string('answers', 'magtest'));            
            foreach ($cats as $cat)
            {
            $answer_txt = get_string('category','mod_magtest')." '".$cat->name."' answer";        
            $question_editor = $mform->addElement('editor', 'questionanswer['.$cat->id.']', $answer_txt,null,array('maxfiles' => EDITOR_UNLIMITED_FILES,
                            'noclean' => true, 'context' =>  $mod_context));
           
            }
            
        }
        else if ($this->cmd == 'update')
        {
            $mform->addElement('hidden', 'cmd', 'update');
            $mform->addElement('hidden', 'qid', $question->id); 
              
             
            $questiontext_editor  = $mform->addElement('editor', 'questiontext',get_string('question_text','magtest'));
            $questiontext_editor->setValue(array('text'=>  $question->questiontext));
            
            //insert hte question .
            //get the categories 
            $cats = $DB->get_records('magtest_category',array('magtestid'=>$magtest->id));          
            $mform->addElement('header', 'header1', get_string('answers', 'magtest'));            
            foreach ($cats as $cat)
            {        
            
                $question_answer_text = get_string('category','mod_magtest')." '".$cat->name."' answer";
                $question_editor = $mform->addElement('editor', 'questionanswer['.$cat->id.']',$question_answer_text,null,array('maxfiles' => EDITOR_UNLIMITED_FILES,
                            'noclean' => true, 'context' =>  $mod_context));
                if(!empty(  $answers[$cat->id]))
                {            
                 $question_editor->setValue(array('text'=>  $answers[$cat->id]));
                }
            }
        }
        
          $this->add_action_buttons();
    }
}
