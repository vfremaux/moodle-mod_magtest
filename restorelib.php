<?php //$Id: restorelib.php,v 1.2 2012-07-07 16:47:53 vf Exp $

/**
* @package mod-magtest
* @category mod
* @author Valery Fremaux > 1.8
* @date 16/05/2009
*
* Restore library for module magtest
*/

    //This php script contains all the stuff to backup/restore
    //magtest mods

    //This is the "graphical" structure of the magtest mod:
    //
    //           magtest                                  
    //          (CL,pk->id)               
    //               |
    //               |                       
    //               +---------------------------------------+
    //               |                                       |
    //         magtest_question                        magtest_category 
    //  (IL, pk->id, fk->magtestid)              (IL, pk->id, fk->magtestid)
    //               |                                       |
    //               +---------------------------------------+
    //                                  |
    //                            magtest_answer
    //                 (IL, pk->id, fk->magtestid*, fk->questionid, fk->categoryid)
    //                                  |
    //                            magtest_useranswer
    //    (UL, pk->id, fk->magtestid*, fk->answerid, fk->userid, fk->questionid*)
    //
    // (*) Redundancies for query optimisation.
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          IL->instance level info
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files
    //
    //-----------------------------------------------------------

    //This function executes all the restore procedure about this mod
    function magtest_restore_mods($mod, $restore) {
        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;
            //traverse_xmlize($info);                                                                     //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //Now, build the MAGTEST record structure
            $magtest->course = $restore->course_id;
            $magtest->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $magtest->description = backup_todb($info['MOD']['#']['DESCRIPTION']['0']['#']);
            $magtest->type = backup_todb($info['MOD']['#']['TYPE']['0']['#']);  
            $magtest->starttime = backup_todb($info['MOD']['#']['STARTTIME']['0']['#']);  
            $magtest->starttimeenable = backup_todb($info['MOD']['#']['STARTTIMEENABLE']['0']['#']);  
            $magtest->endtime = backup_todb($info['MOD']['#']['ENDTIME']['0']['#']);  
            $magtest->endtimeenable = backup_todb($info['MOD']['#']['ENDTIMEENABLE']['0']['#']);  
            $magtest->timecreated = backup_todb($info['MOD']['#']['TIMECREATED']['0']['#']); 
            $magtest->result = backup_todb($info['MOD']['#']['RESULT']['0']['#']);
            $magtest->weighted = backup_todb($info['MOD']['#']['WEIGHTED']['0']['#']);
            $magtest->usemakegroups = backup_todb($info['MOD']['#']['USEMAKEGROUPS']['0']['#']);
            $magtest->pagesize = backup_todb($info['MOD']['#']['PAGESIZE']['0']['#']);
            $magtest->allowreplay = backup_todb($info['MOD']['#']['ALLOWREPLAY']['0']['#']);

            //The structure is equal to the db, so insert the magtest
            $newid = insert_record ('magtest', $magtest);

            //Do some output
            if (! defined('RESTORE_SILENTLY')) {
	            echo "<li>".get_string('modulename', 'magtest')." \"".format_string(stripslashes($magtest->name),true)."\"</li>";
	        }
            backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,$mod->modtype,
                             $mod->id, $newid);

                // We have to restore the magtestwide elements 
                $status = magtest_categories_restore_mods ($mod->id, $newid, $info, $restore, $restore->course_id);
                $status = magtest_questions_restore_mods ($mod->id, $newid, $info, $restore, $restore->course_id);
                $status = magtest_answers_restore_mods ($mod->id, $newid, $info, $restore, $restore->course_id);

                //Now check if want to restore user data and do it.
                if ($restore->mods['magtest']->userinfo) {
                    //Restore magtest_issue
                    $status = magtest_useranswers_restore_mods ($mod->id, $newid, $info, $restore, $restore->course_id);
                }
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }
        return $status;
    }

    //This function restores the magtest elements
    function magtest_categories_restore_mods($old_magtest_id, $new_magtest_id, $info, $restore, $new_course_id) {
        global $CFG;

        $status = true;

        //Get the cats array if exists
        if ($cats = @$info['MOD']['#']['CATEGORIES']['0']['#']['CATEGORY']){

	        //Iterate over cats
	        for($i = 0; $i < sizeof($cats); $i++) {
	            $cat_info = $cats[$i];
	            //traverse_xmlize($cat_info);                                                         //Debug
	            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
	            //$GLOBALS['traverse_array']="";                                                              //Debug
	
	            //We'll need this later!!
	            $oldid = backup_todb($cat_info['#']['ID']['0']['#']);
	
	            //Now, build the magtest_category record structure
	            $cat->magtestid = $new_magtest_id;
	            $cat->name = backup_todb($cat_info['#']['NAME']['0']['#']);
	            $cat->format = backup_todb($cat_info['#']['FORMAT']['0']['#']);  
	            $cat->description = backup_todb($cat_info['#']['DESCRIPTION']['0']['#']);  
	            $cat->result = backup_todb($cat_info['#']['RESULT']['0']['#']); 
	            $cat->sortorder = backup_todb($cat_info['#']['SORTORDER']['0']['#']); 
	            $cat->symbol = backup_todb($cat_info['#']['SYMBOL']['0']['#']); 
	            
	            //The structure is equal to the db, so insert the magtest category
	            $newid = insert_record ('magtest_category', $cat);
	
	            //Do some output
	            if (($i+1) % 50 == 0) {
	            	if (! defined('RESTORE_SILENTLY')) {
		                echo ".";
		                if (($i+1) % 1000 == 0) {
		                    echo "<br />";
		                }
		            }
	                backup_flush(300);
	            }
	
	            if ($newid) {
	                //We have the newid, update backup_ids
	                backup_putid($restore->backup_unique_code, 'magtest_category', $oldid, $newid);
	            } else {
	                $status = false;
	            }
	        }
	    }
        return $status;
    }

    //This function restores the magtest questions
    function magtest_questions_restore_mods($old_magtest_id, $new_magtest_id, $info, $restore) {
        global $CFG;

        $status = true;

        //Get the question array if exists
        if ($questions = @$info['MOD']['#']['QUESTIONS']['0']['#']['QUESTION']){

	        //Iterate over questions
	        for($i = 0; $i < sizeof($questions); $i++) {
	            $question_info = $questions[$i];
	            //traverse_xmlize($question_info);                                                         //Debug
	            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
	            //$GLOBALS['traverse_array']="";                                                              //Debug
	
	            //We'll need this later!!
	            $oldid = backup_todb($question_info['#']['ID']['0']['#']);
	
	            //Now, build the magtest_question record structure
	            $question->magtestid = $new_magtest_id;
	            $question->questiontext = backup_todb($question_info['#']['QUESTIONTEXT']['0']['#']);  
	            $question->format = backup_todb($question_info['#']['FORMAT']['0']['#']);  
	            $question->sortorder = backup_todb($question_info['#']['SORTORDER']['0']['#']);  
	            
	            //The structure is equal to the db, so insert the magtest question
	            $newid = insert_record ('magtest_question', $question);
	
	            //Do some output
	            if (($i+1) % 50 == 0) {
	            	if (! defined('RESTORE_SILENTLY')) {
		                echo ".";
		                if (($i+1) % 1000 == 0) {
		                    echo "<br />";
		                }
		            }
	                backup_flush(300);
	            }
	
	            if ($newid) {
	                //We have the newid, update backup_ids
	                backup_putid($restore->backup_unique_code, 'magtest_question', $oldid, $newid);
	            } else {
	                $status = false;
	            }
	        }
	    }
        return $status;
    }

    //This function restores the magtest answers in a magtest instance
    function magtest_answers_restore_mods($old_magtest_id, $new_magtest_id, $info, $restore) {
        global $CFG;

        $status = true;

        //Get the answers array if exists
        if (@$answers = $info['MOD']['#']['ANSWERS']['0']['#']['ANSWER']){

	        //Iterate over used elements
	        for($i = 0; $i < sizeof($answers); $i++) {
	            $answer_info = $answers[$i];
	            //traverse_xmlize($anwer_info);                                                         //Debug
	            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
	            //$GLOBALS['traverse_array']="";                                                              //Debug
	
	            //We'll need this later!!
	            $oldid = backup_todb($answer_info['#']['ID']['0']['#']);
	
	            //Now, build the magtest_ELEMENTUSED record structure
	            $answer->magtestid = $new_magtest_id;
	            $answer->questionid = backup_todb($answer_info['#']['QUESTIONID']['0']['#']);
	            $answer->answertext = backup_todb($answer_info['#']['ANSWERTEXT']['0']['#']);  
	            $answer->format = backup_todb($answer_info['#']['FORMAT']['0']['#']);  
	            $answer->helper = backup_todb($answer_info['#']['HELPER']['0']['#']);  
	            $answer->helperformat = backup_todb($answer_info['#']['HELPERFORMAT']['0']['#']);  
	            $answer->categoryid = backup_todb($answer_info['#']['CATEGORYID']['0']['#']);  
	            $answer->weight = backup_todb($answer_info['#']['WEIGHT']['0']['#']);  
	
	            //We have to recode the questionid field
	            $question = backup_getid($restore->backup_unique_code, 'magtest_question', $answer->questionid);
	            if ($question) {
	                $answer->questionid = $question->new_id;
	            }
	
	            //We have to recode the category field
	            $cat = backup_getid($restore->backup_unique_code, 'magtest_category', $answer->categoryid);
	            if ($cat) {
	                $answer->categoryid = $cat->new_id;
	            }
	            
	            //The structure is equal to the db, so insert the answer
	            $newid = insert_record ('magtest_answer', $answer);
	
	            //Do some output
	            if (($i+1) % 50 == 0) {
	            	if (! defined('RESTORE_SILENTLY')) {
		                echo ".";
		                if (($i+1) % 1000 == 0) {
		                    echo "<br />";
		                }
		            }
	                backup_flush(300);
	            }
	
	            if ($newid) {
	                //We have the newid, update backup_ids
	                backup_putid($restore->backup_unique_code, 'magtest_answer', $oldid, $newid);
	            } else {
	                $status = false;
	            }
	        }
	    }
        return $status;
    }

    //This function restores the answers of real users to the test
    function magtest_useranswers_restore_mods($old_magtest_id, $new_magtest_id, $info, $restore) {
        global $CFG;

        $status = true;

        //Get the issues array
        if (!array_key_exists('USERANSWERS', $info['MOD']['#'])) return true;
        $choices = $info['MOD']['#']['USERANSWERS']['0']['#']['USERCHOICE'];

        //Iterate over choices
        for($i = 0; $i < sizeof($choices); $i++) {
            $choice_info = $choices[$i];
            //traverse_xmlize($choice_info);                                                               //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($issue_info['#']['ID']['0']['#']);

            //Now, build the magtest_useranswer record structure
            $choice->magtestid = $new_magtest_id;
            $choice->anwerid = backup_todb($choice_info['#']['ANSWERID']['0']['#']);  
            $choice->userid = backup_todb($choice_info['#']['USERID']['0']['#']);  
            $choice->questionid = backup_todb($choice_info['#']['QUESTIONID']['0']['#']);  
            $choice->timeanswered = backup_todb($choice_info['#']['TIMEANSWERED']['0']['#']);  

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code, 'user', $choice->userid);
            if ($user) {
                $choice->userid = $user->new_id;
            }

            //We have to recode the questionid field
            $question = backup_getid($restore->backup_unique_code, 'magtest_question', $choice->questionid);
            if ($question) {
                $choice->questionid = $question->new_id;
            }

            //We have to recode the answerid field
            $answer = backup_getid($restore->backup_unique_code, 'magtest_answer', $choice->answerid);
            if ($answer) {
                $choice->answerid = $answer->new_id;
            }

            //The structure is equal to the db, so insert the user's answer
            $newid = insert_record ('magtest_useranswer', $choice);

            //Do some output
            if (($i+1) % 50 == 0) {
            	if (! defined('RESTORE_SILENTLY')) {
	                echo ".";
	                if (($i+1) % 1000 == 0) {
	                    echo "<br />";
	                }
	            }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'magtest_useranswer', $oldid, $newid);
            } else {
                $status = false;
            }
        }
        return $status;
    }

    // Returns a content decoded to support interactivities linking. Every module
    // should have its own. They are called automatically from
    // xxxxxx_decode_content_links_caller() function in each module
    // in the restore process
    function magtest_decode_content_links ($content, $restore) {            
        global $CFG;
            
        $result = $content;
                
        $searchstring = '/\$@(MAGTEST)@\$\//';
        $result = preg_replace($searchstring, $CFG->wwwroot.'/mod/magtest/', $result);

        return $result;
    }

    // Tries to rework all the textual content of the module that might
    // contain transcoded backup references upon other intances.
    // will call magtest_decode_content_links() on all instances that are detected
    // in content. This must be applied to all newly resotred instances of 
    // magtest in the current course.
    function magtest_decode_content_links_caller($restore) {
        global $CFG;
        $status = true;

        $sql = "
            SELECT 
                m.id, 
                m.description,
                m.result
            FROM 
                {$CFG->prefix}magtest m
            WHERE 
                m.course = $restore->course_id
        ";
        if ($magtests = get_records_sql($sql)) {
            $i = 0;   //Counter to send some output to the browser to avoid timeouts
            foreach ($magtests as $magtest) {
                //Increment counter
                $i++;
                $content = $magtest->description;
                $result = restore_decode_content_links_worker($content, $restore);

                if ($result != $content) {
                    //Update record
                    $magtest->description = addslashes($result);
                    $status = update_record('magtest', $magtest);
                    if (debugging()) {
                        if (!defined('RESTORE_SILENTLY')) {
                            echo '<br /><hr />'.s($content).'<br />changed to<br />'.s($result).'<hr /><br />';
                        }
                    }
                }

                $content = $magtest->result;
                $result = restore_decode_content_links_worker($content, $restore);

                if ($result != $content) {
                    //Update record
                    $magtest->result = addslashes($result);
                    $status = update_record('magtest', $magtest);
                    if (debugging()) {
                        if (!defined('RESTORE_SILENTLY')) {
                            echo '<br /><hr />'.s($content).'<br />changed to<br />'.s($result).'<hr /><br />';
                        }
                    }
                }
                
                // TODO : Examinate other tectual contents such as questiontexts, answertexts
                // and category descriptions and result field.
                
                //Do some output
                if (($i+1) % 5 == 0) {
                    if (!defined('RESTORE_SILENTLY')) {
                        echo ".";
                        if (($i+1) % 100 == 0) {
                            echo "<br />";
                        }
                    }
                    backup_flush(300);
                }
            }
        }
        return $status;
    }

    // TODO : Exmainate logs and implement the magtest_restore_logs() function
?>
