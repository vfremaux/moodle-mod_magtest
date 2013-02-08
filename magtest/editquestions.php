<?php

	include '../../config.php';
    include_once $CFG->dirroot.'/mod/magtest/forms/addquestions_form.php';
	include_once $CFG->dirroot.'/mod/magtest/class/magtest.class.php';

    $id = required_param('id', PARAM_INT);                                 
	$qid = required_param('qid', PARAM_INT); // question id
	$howmany = optional_param('howmany', 1, PARAM_INT);

    if ($id) {
		if (! $cm = get_coursemodule_from_id('magtest', $id)) {
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
    
    $mod_context = context_module::instance($id);
    
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
    $maxbytes = 1024 * 1024 * 1000 ; //100 mb TODO: add settings
    $questionoptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => 100, 'maxbytes' => $maxbytes, 'context' => $mod_context);
    $answeroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => 100, 'maxbytes' => $maxbytes, 'context' => $mod_context);

    //   DebugBreak();
	if ($data = $form->get_data()){
	
        $cmd = $data->cmd ; 

        if ($cmd == 'add') {
         
            $data = file_postupdate_standard_editor($data, 'questiontext', $questionoptions, $mod_context, 'mod_magtest', 'question', 0);
                                           
            $question = new stdClass();
            $question->questiontext = $data->questiontext; 
            $question->questiontextformat = FORMAT_MOODLE;
            $question->magtestid = $data->magtestid;
            $maxsort = 0 + $DB->get_field('magtest_question', 'MAX(sortorder)', array('magtestid' => $data->magtestid));
            $question->sortorder = $maxsort + 1;
            $new_answer_id = $DB->insert_record('magtest_question', $question);
            
            //store the cats answers 
            foreach ($data->cats as $catid) {               
				$data = file_postupdate_standard_editor($data, 'questionanswer'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'questionanswer', $new_answer_id); 
				$data = file_postupdate_standard_editor($data, 'helper'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'helper', $new_answer_id); 
               	$answer = new stdClass();
				$answer->questionid = $new_answer_id; 
				$answer->magtestid = $magtest->id;
				$answer->answertextformat = FORMAT_HTML;
				$answer->answertext = $data->{'questionanswer'.$catid} ;
				$answer->helperformat = FORMAT_HTML;
				$answer->helper = $data->{'helper'.$catid} ;
				$weightkey = 'weight'.$catid;
				$answer->weight = $data->$weightkey;
				$answer->categoryid = $catid;
				$DB->insert_record('magtest_answer', $answer);
            }            
        } else {
//            DebugBreak();
			//update question
			
			$data = file_postupdate_standard_editor($data, 'questiontext', $questionoptions, $mod_context, 'mod_magtest', 'question', 0);
			$question = $DB->get_record('magtest_question',array('id' => $data->qid));
			$question->questiontext = $data->questiontext ;
			$question->questiontextformat = FORMAT_HTML ;
			$DB->update_record('magtest_question', $question);
   
			foreach ($data->cats as $catid) {

				//try load the answer 
                $old_answer = $DB->get_record('magtest_answer', array('questionid' => $qid, 'categoryid' => $catid));
                 
                if($old_answer){
                	//do an update
                    $data = file_postupdate_standard_editor($data, 'questionanswer'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'questionanswer', $old_answer->id);     
                    $old_answer->answertext = $data->{'questionanswer'.$catid};

                    $data = file_postupdate_standard_editor($data, 'helper'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'helper', $old_answer->id);     
                    $old_answer->helper = $data->{'helper'.$catid};
                    $old_answer->helperformat = FORMAT_MOODLE;
                    
                    $weightkey = 'weight'.$catid;
                    $old_answer->weight = $data->$weightkey;

                    $DB->update_record('magtest_answer', $old_answer);
                } else {
					//insert a new record 
                    $new_answer = new stdClass();
                    $new_answer->id = null;
                   
                    $new_answer->questionid = $qid;
                    $new_answer->categoryid = $catid ;
                    $new_answer->answertext = ''; // $data->{'questionanswer'.$catid};
                    $new_answer->magtestid = $data->magtestid;
                    $weightkey = 'weight'.$catid;
                    $new_answer->weight = $data->$weightkey;
                    $new_answer->id = $DB->insert_record('magtest_answer', $new_answer);
                    $data = file_postupdate_standard_editor($data, 'questionanswer'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'questionanswer', $new_answer->id); 
                    $new_answer->answertext = $data->{'questionanswer'.$catid};
                    $new_answer->answertextformat = FORMAT_HTML;

                    $data = file_postupdate_standard_editor($data, 'helper'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'helper', $new_answer->id); 
                    $new_answer->helper = $data->{'helper'.$catid};
                    $new_answer->helperformat = FORMAT_HTML;

                    $DB->update_record('magtest_answer', $new_answer);
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
