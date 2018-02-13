<?php
<<<<<<< HEAD

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
    
    require_course_login($course->id, true, $cm);
    
    $mod_context = context_module::instance($id);
    
    $url = $CFG->wwwroot.'/mod/magtest/editquestions.php?id='.$id;
    $editurl = $CFG->wwwroot.'/mod/magtest/view.php?id='.$id.'&amp;view=questions';

    $PAGE->set_title("$course->shortname: $magtest->name");
    $PAGE->set_heading("$course->fullname");
    $PAGE->navbar->add(get_string('questions', 'magtest'), $editurl);
    $PAGE->navbar->add(get_string('addquestion', 'magtest'));
    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(true);
    $PAGE->set_url($url);
    $PAGE->set_button($OUTPUT->update_module_button($cm->id, 'magtest'));
    $PAGE->set_headingmenu(navmenu($course, $cm));
           
    if($qid <= 0){ 
		$form = new Question_Form($magtest, 'add', $howmany, $url);
    } else {
		$form = new Question_Form($magtest, 'update', $howmany, $url);       
    }
    $maxbytes = 1024 * 1024 * 1000 ; //100 mb TODO: add settings
    $questionoptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => 100, 'maxbytes' => $maxbytes, 'context' => $mod_context);
    $answeroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => 100, 'maxbytes' => $maxbytes, 'context' => $mod_context);

    if ($form->is_cancelled()){
    	redirect($editurl);
    }

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
         		if (!$magtest->singlechoice){
					$data = file_postupdate_standard_editor($data, 'questionanswer'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'questionanswer', $new_answer_id); 
					$data = file_postupdate_standard_editor($data, 'helper'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'helper', $new_answer_id); 
				}
               	$answer = new stdClass();
				$answer->questionid = $new_answer_id; 
				$answer->magtestid = $magtest->id;
         		if (!$magtest->singlechoice){
					$answer->answertextformat = FORMAT_HTML;
					$answer->answertext = $data->{'questionanswer'.$catid} ;
					$answer->helperformat = FORMAT_HTML;
					$answer->helper = $data->{'helper'.$catid} ;
				} else {
					$answer->answertextformat = 0;
					$answer->answertext = '';
					$answer->helperformat = 0;
					$answer->helper = '';
				}
				if ($magtest->weighted){
					$weightkey = 'weight'.$catid;
					$answer->weight = $data->$weightkey;
				} else {
					$answer->weight =  1;
				}
				$answer->categoryid = $catid;
				$DB->insert_record('magtest_answer', $answer);
            }            
        } else {
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
                	if (!$magtest->singlechoice){
	                    $data = file_postupdate_standard_editor($data, 'questionanswer'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'questionanswer', $old_answer->id);     
	                    $old_answer->answertext = $data->{'questionanswer'.$catid};

	                    $data = file_postupdate_standard_editor($data, 'helper'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'helper', $old_answer->id);     
	                    $old_answer->helper = $data->{'helper'.$catid};
	                    $old_answer->helperformat = FORMAT_MOODLE;
	                }
                    
					if ($magtest->weighted){
	                    $weightkey = 'weight'.$catid;
	                    $old_answer->weight = $data->$weightkey;
	                } else {
	                	$old_answer->weight = 1;
	                }

                    $DB->update_record('magtest_answer', $old_answer);
                } else {
					//insert a new record 
                    $new_answer = new stdClass();
                    $new_answer->id = null;
                   
                    $new_answer->questionid = $qid;
                    $new_answer->categoryid = $catid ;
                    $new_answer->answertext = ''; // $data->{'questionanswer'.$catid};
                    $new_answer->helpertext = ''; // $data->{'questionanswer'.$catid};
                    $new_answer->magtestid = $data->magtestid;
                    $new_answer->answertextformat = 0;
                    $new_answer->helpertextformat = 0;
					if ($magtest->weighted){
	                    $weightkey = 'weight'.$catid;
	                    $new_answer->weight = $data->$weightkey;
	                } else {
	                	$new_answer->weight = 1;
	                }
                    $new_answer->id = $DB->insert_record('magtest_answer', $new_answer);
                    
                    if (!$magtest->pluginchoice){
	                    $data = file_postupdate_standard_editor($data, 'questionanswer'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'questionanswer', $new_answer->id); 
	                    $new_answer->answertext = $data->{'questionanswer'.$catid};
	                    $new_answer->answertextformat = FORMAT_HTML;

	                    $data = file_postupdate_standard_editor($data, 'helper'.$catid, $questionoptions, $mod_context, 'mod_magtest', 'helper', $new_answer->id); 
	                    $new_answer->helper = $data->{'helper'.$catid};
	                    $new_answer->helperformat = FORMAT_HTML;
	                }

                    $DB->update_record('magtest_answer', $new_answer);
				}
			}           
        }
        $options['id'] = $id;

    	redirect($editurl);
        exit;
    }
     
	echo $OUTPUT->header();
    $form->display();     
    echo $OUTPUT->footer($course);
=======
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
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @see        categories.controller.php for associated controller.
 */
require('../../config.php');
require_once($CFG->dirroot.'/mod/magtest/forms/addquestions_form.php');
require_once($CFG->dirroot.'/mod/magtest/classes/magtest.class.php');

$id = required_param('id', PARAM_INT); // Course module id.
$qid = required_param('qid', PARAM_INT); // Question id.
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

require_course_login($course->id, true, $cm);

$mod_context = context_module::instance($id);

$url = new moodle_url('/mod/magtest/editquestions.php', array('id' => $id));
$editurl = new moodle_url('/mod/magtest/view.php', array('id' => $id, 'view' => 'questions'));

$PAGE->set_title("$course->shortname: $magtest->name");
$PAGE->set_heading("$course->fullname");
$PAGE->navbar->add(get_string('questions', 'magtest'), $editurl);
$PAGE->navbar->add(get_string('addquestion', 'magtest'));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);
$PAGE->set_url($url);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'magtest'));

if ($qid <= 0) {
    $form = new Question_Form($magtest, 'add', $howmany, $url);
} else {
    $form = new Question_Form($magtest, 'update', $howmany, $url);
}

$maxbytes = 1024 * 1024 * 1000 ; // TODO: add settings.
$questionoptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => 100, 'maxbytes' => $maxbytes, 'context' => $mod_context);
$answeroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => 100, 'maxbytes' => $maxbytes, 'context' => $mod_context);

if ($form->is_cancelled()) {
    redirect($editurl);
}

if ($data = $form->get_data()) {

    $cmd = $data->cmd;

    if ($cmd == 'add') {

        $data = file_postupdate_standard_editor($data, 'questiontext', $questionoptions, $mod_context, 'mod_magtest', 'question', 0);

        $question = new stdClass();
        $question->questiontext = $data->questiontext; 
        $question->questiontextformat = FORMAT_MOODLE;
        $question->magtestid = $data->magtestid;
        $maxsort = 0 + $DB->get_field('magtest_question', 'MAX(sortorder)', array('magtestid' => $data->magtestid));
        $question->sortorder = $maxsort + 1;
        $newanswer_id = $DB->insert_record('magtest_question', $question);

        // Store the cats answers.
        foreach ($data->cats as $catid) {
            if (!$magtest->singlechoice) {
                $data = file_postupdate_standard_editor($data, 'questionanswer'.$catid, $questionoptions, $mod_context,
                                                        'mod_magtest', 'questionanswer', $newanswer_id);
                $data = file_postupdate_standard_editor($data, 'helper'.$catid, $questionoptions, $mod_context, 'mod_magtest',
                                                        'helper', $newanswer_id);
            }

            $answer = new stdClass();
            $answer->questionid = $newanswer_id;
            $answer->magtestid = $magtest->id;

            if (!$magtest->singlechoice) {
                $answer->answertextformat = FORMAT_HTML;
                $answer->answertext = $data->{'questionanswer'.$catid};
                $answer->helperformat = FORMAT_HTML;
                $answer->helper = $data->{'helper'.$catid};
            } else {
                $answer->answertextformat = 0;
                $answer->answertext = '';
                $answer->helperformat = 0;
                $answer->helper = '';
            }

            if ($magtest->weighted) {
                $weightkey = 'weight'.$catid;
                $answer->weight = $data->$weightkey;
            } else {
                $answer->weight = 1;
            }

            $answer->categoryid = $catid;
            $DB->insert_record('magtest_answer', $answer);
        }
    } else {
        // Update question.

        $data = file_postupdate_standard_editor($data, 'questiontext', $questionoptions, $mod_context, 'mod_magtest',
                                                'question', 0);
        $question = $DB->get_record('magtest_question',array('id' => $data->qid));
        $question->questiontext = $data->questiontext;
        $question->questiontextformat = FORMAT_HTML;
        $DB->update_record('magtest_question', $question);

        foreach ($data->cats as $catid) {

            // Try load the answer.
            $oldanswer = $DB->get_record('magtest_answer', array('questionid' => $qid, 'categoryid' => $catid));

            if ($oldanswer) {
                // Do an update.
                if (!$magtest->singlechoice) {
                    $data = file_postupdate_standard_editor($data, 'questionanswer'.$catid, $questionoptions, $mod_context,
                                                            'mod_magtest', 'questionanswer', $oldanswer->id);
                    $oldanswer->answertext = $data->{'questionanswer'.$catid};

                    $data = file_postupdate_standard_editor($data, 'helper'.$catid, $questionoptions, $mod_context,
                                                            'mod_magtest', 'helper', $oldanswer->id);
                    $oldanswer->helper = $data->{'helper'.$catid};
                    $oldanswer->helperformat = FORMAT_MOODLE;
                }

                if ($magtest->weighted) {
                    $weightkey = 'weight'.$catid;
                    $oldanswer->weight = $data->$weightkey;
                } else {
                    $oldanswer->weight = 1;
                }

                $DB->update_record('magtest_answer', $oldanswer);
            } else {
                // Insert a new record.
                $newanswer = new stdClass();
                $newanswer->id = null;

                $newanswer->questionid = $qid;
                $newanswer->categoryid = $catid;
                $newanswer->answertext = '';
                $newanswer->helpertext = '';
                $newanswer->magtestid = $data->magtestid;
                $newanswer->answertextformat = 0;
                $newanswer->helpertextformat = 0;
                if ($magtest->weighted) {
                    $weightkey = 'weight'.$catid;
                    $newanswer->weight = $data->$weightkey;
                } else {
                    $newanswer->weight = 1;
                }
                $newanswer->id = $DB->insert_record('magtest_answer', $newanswer);

                if (!$magtest->pluginchoice) {
                    $data = file_postupdate_standard_editor($data, 'questionanswer'.$catid, $questionoptions, $mod_context,
                                                            'mod_magtest', 'questionanswer', $newanswer->id);
                    $newanswer->answertext = $data->{'questionanswer'.$catid};
                    $newanswer->answertextformat = FORMAT_HTML;

                    $data = file_postupdate_standard_editor($data, 'helper'.$catid, $questionoptions, $mod_context, 'mod_magtest',
                                                            'helper', $newanswer->id);
                    $newanswer->helper = $data->{'helper'.$catid};
                    $newanswer->helperformat = FORMAT_HTML;
                }

                $DB->update_record('magtest_answer', $newanswer);
            }
        }
    }
    $options['id'] = $id;

    redirect($editurl);
    exit;
}

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer($course);
>>>>>>> MOODLE_34_STABLE
