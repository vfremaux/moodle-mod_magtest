<?php

	include '../../config.php';
    include_once $CFG->dirroot.'/mod/magtest/forms/addquestions_form.php';
	include_once $CFG->dirroot.'/mod/magtest/class/magtest.class.php';

    $id = required_param('id', PARAM_INT);                                 
	$qid = required_param('qid', PARAM_INT);                                 
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
    
    $url = $CFG->wwwroot.'/mod/magtest/editquestions.php?id='.$id;

    $PAGE->set_title("$course->shortname: $magtest->name");
    $PAGE->set_heading("$course->fullname");
    /* SCANMSG: may be additional work required for $navigation variable */
    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(true);
    $PAGE->set_url($url);
    $PAGE->set_button($OUTPUT->update_module_button($cm->id, 'magtest'));
    $PAGE->set_headingmenu(navmenu($course, $cm));
    echo $OUTPUT->header();
           
    if($qid <= 0){ 
 	 	$form = new Question_Form($magtest, 'add', $howmany, $url);
    } else {
     	$form = new Question_Form($magtest, 'update', $howmany, $url);       
    }

    //   DebugBreak();
	if ($data = $form->get_data()){
 	    
        $cmd = $data->cmd ; 
      
        if ($cmd == 'add'){           
            $question = new stdClass();
            $question->questiontext = $data->questiontext['text'];
            $question->questiontextformat =  FORMAT_MOODLE;
            $question->magtestid = $data->magtestid;
            $maxsort = 0 + $DB->get_field('magtest_question', 'MAX(sortorder)', array('magtestid'=>$data->magtestid));
            $question->sortorder = $maxsort + 1;
           
            
            $new_answer_id = $DB->insert_record('magtest_question', $question);
            
            //store the cats answers 
            foreach($data->questionanswer as $catid => $anstext){
               	$answer = new stdClass();
               	$answer->questionid = $new_answer_id; 
               	$answer->magtestid =  $magtest->id;
               	$answer->answertextformat =  FORMAT_MOODLE;
               	$answer->answertext = $anstext['text'] ;
           
               	$answer->categoryid = $catid;
               
               	$DB->insert_record('magtest_answer',$answer);
            }
            
        } else {
          
           //update question
           $question = $DB->get_record('magtest_question',array('id' => $data->qid));
           $question->questiontext = $data->questiontext['text'] ;
           $question->questiontextformat = FORMAT_MOODLE ;
           $DB->update_record('magtest_question', $question);
           
           foreach ($data->questionanswer as $catid => $anstext) {
             	//try load the answer 
             	$old_answer = $DB->get_record('magtest_answer',array('questionid' => $qid,'categoryid' => $catid));
             	if($old_answer){
                 	//do an update 
                 	$old_answer->answertext = $anstext['text'];
                 	$DB->update_record('magtest_answer',$old_answer);
             	} else {
                 	//insert a new record 
                 	$new_answer = new stdClass();
                 	$new_answer->questionid = $qid;
                 	$new_answer->categoryid = $catid ;
                 	$new_answer->answertext = $anstext['text'];
                 	$new_answer->magtestid = $data->magtestid;
                 
                 	$DB->insert_record('magtest_answer', $new_answer);
             	}
           	}           
        }
		$options['id'] = $id;

        echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot.'/mod/magtest/view.php', $options));         

        echo $OUTPUT->footer($course);                                        
        exit;
    }
 	
 	$form->display();
 	
    echo $OUTPUT->footer($course);
