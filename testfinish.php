<?php

    if (!(isset($id) and $view === 'doit' and  has_capability('mod/magtest:doit', $context))) {
  	    print 'You have not to see this page';
  	    exit;
    }

    $useranswers = get_magtest_useranswers($magtest->id, $USER->id);

    if (! $useranswers ) {
   	    echo $OUTPUT->notification(get_string('nouseranswer', 'magtest'));
  	    exit;
    }

    $categories = get_magtest_categories($magtest->id);
    $questions = get_magtest_questions($magtest->id);

    $count_cat = array();
    $nb_total = 0;

    // Prepare the arrray to print the table 
    $table->head = array(' ');

    // Prepare information relative to the categories in the final table
    foreach($categories as $category) {
  	    $table->head[] = $category->categoryshortname;
  	    $tab[$category->categoryshortname] = 0; 
  	    $tab_cat[$category->categoryshortname] = $category;
    }

    // Cumulation of the nb of answer in each categorie

    foreach($useranswers as $useranswer) {
	    $cat = $categories[$questions[$useranswer->questionid]->answers[$useranswer->answerid]->categoryid];
  	    $tab[$cat->categoryshortname] = $tab[$cat->categoryshortname] + 1 ;  
    }

    $results = array();

    $results[] = array_merge(
			     array(
			           $userpic = new user_picture();
			           $userpic->user = $USER;
			           $userpic->courseid = $course->id;
			           $userpic->image->src = true;
			           'user'=>$OUTPUT->user_picture($userpic).
			           fullname($USER, has_capability('moodle/site:viewfullnames', $context))
			           ),
			     $tab
			     );

    $results[] = array(s($tab_cat[array_search(max($tab),$tab)]->categorytext));
    $table->data = $results;
    echo html_writer::table($table);
?>