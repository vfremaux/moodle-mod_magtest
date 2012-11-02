<?php

include_once $CFG->libdir.'/formslib.php';
include_once $CFG->dirroot.'/mod/magtest/locallib.php';

/**
* Form to add or update categories
*
*/

class Category_Form extends moodleform{
	
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
		global $CFG,$catid,$DB,$id;
		
		$mform = $this->_form;

		$mform->addElement('header', 'header0', get_string($this->cmd.'categories', 'magtest'));
		$mform->addElement('hidden', 'catid');
		$mform->addElement('hidden', 'howmany');
		$mform->setDefault('howmany', $this->howmany);
		$mform->addElement('hidden', 'what');
		$mform->setDefault('what', 'do'.$this->cmd);
		
        if($this->cmd == 'add')
        {
            $mform->addElement('hidden', 'cmd', 'add');
		    $categories = magtest_get_categories($this->magtest->id);
		    $categoryids = array_keys($categories);
            for($i = 0 ; $i < $this->howmany ; $i++){
                $num = $i+1;
            $mform->addElement('static', 'header_'.$num, '<h2>'.get_string('category', 'magtest')." ".$num.'</h2>');
            $mform->addElement('text', 'catname_'.$num, get_string('name'), '', array('size' => '120', 'maxlength' => '255'));
           
            $symboloptions = magtest_get_symbols($magtest, $renderingpathbase);
      
            $mform->addElement('selectgroups','catsymbol_'.$num, get_string('symbol','mod_magtest'),$symboloptions);
              
            //DebugBreak();
          //  $mform->addGroup( $group,'catsymbol');//$group, 'catsymbol', '', array('&nbsp;'), true);
             
             
            $mod_context = get_context_instance(CONTEXT_MODULE,$id); 
         //      DebugBreak();          
            $catdesc_editor = $mform->addElement('editor', 'catdescription_'.$num, get_string('description'),null,array('maxfiles' => EDITOR_UNLIMITED_FILES,
                        'noclean' => true, 'context' =>  $mod_context));
                
            $catresult_editor  = $mform->addElement('editor', 'catresult_'.$num, get_string('categoryresult', 'magtest'),null,array('maxfiles' => EDITOR_UNLIMITED_FILES,
                        'noclean' => true, 'context' =>  $mod_context));
            
            if ($this->magtest->usemakegroups){
                $mform->addElement('text', 'outputgroupname_'.$num, get_string('outputgroupname', 'magtest'), '', array('size' => '128', 'maxlength' => '255'));
                $mform->addElement('text', 'outputgroupdesc_'.$num, get_string('outputgroupdesc', 'magtest'), '', array('size' => '255', 'maxlength' => '255'));
            }  
                
            }
              
		   /* for($i = 0 ; $i < count($categories) + $this->howmany ; $i++){
			    $num = $i + 1;
			    $mform->addElement('static', 'header'.($num), '<h2>'.get_string('category', 'magtest').' '.$num.'</h2>');
			    $mform->addElement('text', 'name'.$num, get_string('name'), '', array('size' => '120', 'maxlength' => '255'));

			    $symboloptions = magtest_get_symbols($magtest, $renderingpathbase);
                $symbolpath = (!empty($categories[@$categoryids[$i]]->symbol)) ? $CFG->wwwroot.'/mod/magtest/pix/symbols/'. $categories[$categoryids[$i]]->symbol : $CFG->wwwroot.'/mod/magtest/pix/symbols/blank.gif' ;
			    $group[0] = $mform->createElement('select', 'symbol'.$num, get_string('symbol', 'magtest'), $symboloptions);
			    $group[1] = &$mform->createElement('html', "<img src=\"$symbolpath\" id=\"symbol_img{$i}\" align=\"bottom\" />");
			    $mform->addGroup($group, 'catsymbol', '', array('&nbsp;'), true);

			    $mform->addElement('editor', 'description'.$num, get_string('description'));
			    $mform->addElement('editor', 'result'.$num, get_string('categoryresult', 'magtest'));

		        if ($this->magtest->usemakegroups){
				    $mform->addElement('text', 'outputgroupname'.$num, get_string('outputgroupname', 'magtest'), '', array('size' => '128', 'maxlength' => '255'));
				    $mform->addElement('text', 'outputgroupdesc'.$num, get_string('outputgroupdesc', 'magtest'), '', array('size' => '255', 'maxlength' => '255'));
		        }
		    }*/
        }
        else if ($this->cmd == 'update')
        {
            $mform->addElement('hidden', 'cmd', 'update');
           
            //LOAD CURRENT CAT
            $category = $DB->get_record('magtest_category',array('id'=>$catid));
            if(empty($category))
            {
                print_error("Invalid Category.");
            }
            
            $mform->addElement('hidden', 'catid', $category->id);
            
            $mform->addElement('static', 'header', '<h2>'.get_string('category', 'magtest').'</h2>');
            $mform->addElement('text', 'catname', get_string('name'), '', array('size' => '120', 'maxlength' => '255'));
            $mform->setDefault('catname',$category->name);
           
            $op = array('Google'=>array('apps'=>2));
         
                
            $symboloptions = magtest_get_symbols($magtest, $renderingpathbase);
            $selectgroup = $mform->addElement('selectgroups','symbol',get_string('symbol','mod_magtest'),$symboloptions);
            $selectgroup->setValue($category->symbol);
           
            $mod_context = get_context_instance(CONTEXT_MODULE,$id); 
       
            $catdesc_editor = $mform->addElement('editor', 'catdescription', get_string('description'),null,array('maxfiles' => EDITOR_UNLIMITED_FILES,
                        'noclean' => true, 'context' =>  $mod_context));
            $catdesc_editor->setValue(array('text'=>$category->description));                      
                
            $catresult_editor  = $mform->addElement('editor', 'catresult', get_string('categoryresult', 'magtest'),null,array('maxfiles' => EDITOR_UNLIMITED_FILES,
                        'noclean' => true, 'context' =>  $mod_context));
            $catresult_editor->setValue(array('text'=>$category->result));                      
            
            if ($this->magtest->usemakegroups){
                $mform->addElement('text', 'outputgroupname', get_string('outputgroupname', 'magtest'), '', array('size' => '128', 'maxlength' => '255'));
                $mform->addElement('text', 'outputgroupdesc', get_string('outputgroupdesc', 'magtest'), '', array('size' => '255', 'maxlength' => '255'));
            }  
        }
        
          $this->add_action_buttons();
	}
}
