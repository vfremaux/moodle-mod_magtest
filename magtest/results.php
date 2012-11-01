<?php

if (!(isset($id) and $view === 'results' and  has_capability('mod/magtest:viewotherresults', $context))) {
  print 'You have not to see this page';
  exit;
 }

require_once($CFG->libdir.'/tablelib.php');

$currentgroup = groups_get_course_group($course, true);
$grouptoshow = optional_param('group', $currentgroup, PARAM_INT);
$groupmode = groups_get_course_groupmode($course);   // Groups are being used
$isseparategroups = ($course->groupmode == SEPARATEGROUPS and $course->groupmodeforce and !has_capability('moodle/site:accessallgroups', $context));

if ($isseparategroups and (!$currentgroup) ) {
  	echo $OUTPUT->notification('You are not in a group.');
  	exit;
}

$baseurl = "view.php?id={$cm->id}&amp;view=results";
groups_print_course_menu($course,$baseurl);

//groups_get_members($groupid, $fields='u.*', $sort='lastname ASC') ;

/*get_users_by_capability($context, $capability, $fields='', $sort='',
        $limitfrom='', $limitnum='', $groups='', $exceptions='', $doanything=true,
        $view=false, $useviewallgroups=false) */
if (! $grouptoshow ) {
  	$grouptoshow = '';
}


$users = get_users_by_capability($context, 'mod/magtest:doit','', '', '', '', $grouptoshow);
$usersanswers = get_magtest_usersanswers($magtest->id);

if (! $usersanswers ) {
  	echo $OUTPUT->notification(get_string('nouseranswer','magtest'));
  	exit;
}

$categories = get_magtest_categories($magtest->id);
$questions = get_magtest_questions($magtest->id);
$count_cat = array();

$nb_total = 0;

foreach($usersanswers as $useranswer) {  
  	$cat = $categories[$questions[$useranswer->questionid]->answers[$useranswer->answerid]->categoryid];
  	$count_cat[$useranswer->userid][$cat->categoryshortname] = $count_cat[$useranswer->userid][$cat->categoryshortname] + 1 ;
}

$table->head = array(get_string('users'));

foreach($categories as $category) {
  $table->head[] = $category->categoryshortname;
  $tab_empty[$category->categoryshortname] = 0; 
}

$results = array();
foreach($users as $user) {

  $results[$user->id] = array_merge(
	  array(
		$userpic = new user_picture();
		$userpic->user = $user;
		$userpic->courseid = $course->id;
		$userpic->image->src = true;
		'user' => $OUTPUT->user_picture($userpic).
		fullname($user, has_capability('moodle/site:viewfullnames', $context))
		),
	  	$tab_empty,
	  	$count_cat[$user->id] );
}


$table->data = $results;

echo html_writer::table($table);
?>