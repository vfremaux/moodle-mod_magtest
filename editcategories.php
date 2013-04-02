<?php

	include '../../config.php';
    include_once $CFG->dirroot.'/mod/magtest/forms/addcategories_form.php';
	include_once $CFG->dirroot.'/mod/magtest/class/magtest.class.php';

    $id = required_param('id', PARAM_INT);                                 
	$catid = required_param('catid', PARAM_INT);                                 
	$howmany = optional_param('howmany', 1, PARAM_INT);

    if ($id) {
        if (! $cm = get_coursemodule_from_id('magtest', $id)) {
            print_error('invalidcoursemodule');
        }
        if (! $cm = $DB->get_record('course_modules', array('id' => $id))) {
            print_error('invalidcoursemodule');
        }
        if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
            print_error('coursemisconf');
        }
        if (! $magtest = $DB->get_record('magtest', array('id' => $cm->instance))) {
            print_error('invalidcoursemodule');
        }
    }

    require_login($course->id);
    
    $url = $CFG->wwwroot.'/mod/magtest/editcategories.php?id='.$id;

    if($catid <= 0) { 
		$form = new Category_Form($magtest, 'add', $howmany, $url);
    } else {
		$form = new Category_Form($magtest, 'update', $howmany, $url);       
    }
    
	if ($form->is_cancelled()){
		redirect($CFG->wwwroot.'/mod/magtest/view.php?id='.$id);
	}

    $PAGE->set_title("$course->shortname: $magtest->name");
    $PAGE->set_heading("$course->fullname");
    /* SCANMSG: may be additional work required for $navigation variable */
    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(true);
    $PAGE->set_url($url);
    $PAGE->set_button($OUTPUT->update_module_button($cm->id, 'magtest'));
    $PAGE->set_headingmenu(navmenu($course, $cm));
    echo $OUTPUT->header();
           
	if ($data = $form->get_data()) {
        $cmd = $data->cmd ; 
        
        if($cmd == 'add'){  
                              
            $count = $data->howmany;
            
            for($i = 1; $i <= $count ;$i++){

            	$cat = new StdClass();
            	
             	$var = 'catname_'.$i;
             	$cat->name = $data->{$var};
             
				if ($cat->name == '' || empty($cat->name)){   //unfilled category, ignore it .
					continue;
				}
             
				$var = 'catsymbol_'.$i;
	            $cat->symbol = $data->{$var};//$data->catsymbol[$var];

				$var = 'catdescription_'.$i;
				$cat->description = $data->{$var}['text'];   

				$var = 'catdescriptionformat_'.$i;
				$cat->descriptionformat = 1; //$data->{$var}['format'];   
             
                $var = 'catresult_'.$i;
                $cat->result = $data->{$var}['text'];  
                 
                $var = 'outputgroupname_'.$i;
                $cat->outputgroupname = $data->{$var};   

                $var = 'outputgroupdesc_'.$i;
                $cat->outputgroupdesc = $data->{$var};   
         
                $catid = magtest::addCategory($magtest->id, $cat) ;
             
                if(!$catid){
                     print_error("an error occured while adding new category.");
				}
            }
        } else {
        //   DebugBreak();
           //update category 
           $category = $DB->get_record('magtest_category', array('id' => $catid));
           $category->name = $data->catname ; 
           $category->symbol = $data->symbol ; 
           $category->description = $data->catdescription['text'] ; 
           $category->result = $data->catresult['text'] ; 
           $category->outputgroupname = $data->outputgroupname ; 
           $category->outputgroupdesc = $data->outputgroupdesc ; 
           
           $DB->update_record('magtest_category', $category);
        }
        $options['id'] = $id;
        echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot.'/mod/magtest/view.php', $options));
         
        echo $OUTPUT->footer($course);                                        
        exit;
    }
     
     if ($catid >= 0){
       $category = $DB->get_record('magtest_category', array('id' => $catid));
       $form->set_data($category);
     }
     $form->display();
     
    echo $OUTPUT->footer($course);
