<?php
<<<<<<< HEAD

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

// Prepare the arrray to print the table.
$table->head = array(' ');

// Prepare information relative to the categories in the final table.
foreach ($categories as $category) {
    $table->head[] = $category->categoryshortname;
    $tab[$category->categoryshortname] = 0; 
    $tab_cat[$category->categoryshortname] = $category;
}

// Cumulation of the nb of answer in each categorie.

foreach ($useranswers as $useranswer) {
    $cat = $categories[$questions[$useranswer->questionid]->answers[$useranswer->answerid]->categoryid];
    $tab[$cat->categoryshortname] = $tab[$cat->categoryshortname] + 1 ;  
}

$results = array();

$userpic = new user_picture();
$userpic->user = $USER;
$userpic->courseid = $course->id;
$userpic->image->src = true;

$results[] = array_merge(
        array(
            'user' => $OUTPUT->user_picture($userpic).
            fullname($USER, has_capability('moodle/site:viewfullnames', $context))
            ),
        $tab
    );

$results[] = array(s($tab_cat[array_search(max($tab),$tab)]->categorytext));
$table->data = $results;
echo html_writer::table($table);
>>>>>>> MOODLE_34_STABLE
