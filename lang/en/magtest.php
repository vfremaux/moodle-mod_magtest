<?php

$string['<<'] = '<<'; 
$string['>>'] = '>>';
$string['addcategories'] = 'Add categories'; 
$string['addcategory'] = 'Add a category';
$string['addone'] = 'Add one category'; 
$string['addquestion'] = 'Add a question';
$string['addthree'] = 'Add three categories'; 
$string['allowreplay'] = 'Allow replay the test'; 
$string['answercount'] = 'Number of answer';
$string['answerquestions'] = 'Test: '; 
$string['answerweights'] = 'Answer weights'; 
$string['answers'] = 'Answers'; 
$string['answertext'] = 'Answer for category: {$a}'; 
$string['backtocourse'] = 'Back to course';
$string['categories'] = 'Categories';
$string['category'] = 'Category';
$string['categoryresult'] = 'Conclusion test for the category'; 
$string['categoryshortname'] = 'Category short name';
$string['choosecategoryforanswer'] = 'Choose a category for this answer';
$string['closed'] = 'Test is closed. Answering is not any more allowed.'; 
$string['closedtestadvice'] = 'This test is closed. You cannot replay it again.';
$string['commands'] = 'Commands'; 
$string['confirmdeletemessage'] = 'Deleting this question removes all associated answers and all previously made user choices. Continue ?';
$string['delcategory'] = 'Delete the category';
$string['delquestion'] = 'Delete the question';
$string['default'] = 'Default magazine test';
$string['descresult'] = 'Results';
$string['description'] = 'Description'; 
$string['doit'] = 'Perform the test';
$string['editquestions'] = 'Edit the questions';
$string['endtime'] = 'End time'; 
$string['errornotallowed'] = 'You are not allowed to perform this test'; 
$string['erroremptyanswers'] = 'Some answers have no content.<br/>The test may malfunction.';
$string['errorquestionupdate'] = 'Error updating question {$a}';
$string['erroranswerinsert'] = 'Failed inserting a new answer for questionid : {$a}';
$string['errorupdatecategory'] = 'Could not update magtest category {$a}';
$string['guestcannotuse'] = 'Guests cannot use this test';
$string['helpnavigationquestion'] = 'Help';
$string['helper'] = 'Read hint';
$string['intro'] = 'Intro';
$string['magtestattempted'] = 'Attempted on';
$string['magtestaccesses'] = 'Accessed: {$a} times';
$string['magtest:doit'] = 'Make the test';
$string['magtest:manage'] = 'Configure the test';
$string['magtest:addinstance'] = 'Add an instance';
$string['magtest:multipleattempts'] = 'Make multiple attempts';
$string['magtest:viewgeneralstat'] = 'View general statistics';
$string['magtest:viewotherresults'] = 'See results of other participants';
$string['makegroups'] = 'Generate moodle groups from results'; 
$string['modulename'] = 'Multitrack test';
$string['pluginname'] = 'Multitrack test';
$string['pluginadministration'] = 'Magtest Settings';
$string['modulenameplural'] = 'Multitrack tests';
$string['nocategories'] = 'No categories.';
$string['nogroupcreationadvice'] = 'You need having no groups in the course for the group creation feature to work.';
$string['noquestions'] = 'No questions yet.';
$string['notopened'] = 'This test is not opened yet.'; 
$string['nouseranswer'] = 'No user answered';
$string['nousersinthisgroup'] = 'No users in this category';
$string['nocategories'] = 'No categories in the test.';
$string['outputgroupdesc'] = 'Generated group description';
$string['outputgroupname'] = 'Generated group name';
$string['pagenotcomplete'] = 'All questions were not answered.'; 
$string['pagesize'] = 'Questions per page'; 
$string['preview'] = 'preview';
$string['question'] = 'Question'; 
$string['questions'] = 'Questions'; 
$string['questiontext'] = 'Question text'; 
$string['reset'] = 'Replay the test'; 
$string['resetting_data'] = 'Reset magtest responses';
$string['resetting_magtests'] = 'Resetting magazine tests';
$string['result'] = 'Result';
$string['resultformattexttypehelp'] = 'Editing format is identical to the description';
$string['results'] = 'Results';
$string['resultsbycats'] = 'Results per category'; 
$string['resultsbyusers'] = 'Results per user'; 
$string['resulttext'] = 'Global conclusion text'; 
$string['save'] = 'Save'; 
$string['score'] = 'Score'; 
$string['sortorder'] = 'Order'; 
$string['starttime'] = 'Start time'; 
$string['stat'] = 'Statistics';
$string['symbol'] = 'Symbol'; 
$string['testfinish'] = 'You have finished the test';
$string['testnotallok'] = 'The configuration of this test is not complete: you cannot perform it.';
$string['unanswered'] = 'These users did not answer'; 
$string['updatecategory'] = 'Update a category'; 
$string['usemakegroups'] = 'Use this test for generating course groups';
$string['userchoices'] = 'User choices';
$string['weight'] = 'Weight'; 
$string['weighted'] = 'Weighted answers'; 
$string['youneedcreatingcategories'] = 'Before create questions, you have to create at least two categories.';
$string['modulename_help'] = "The Magtest activity provides a multicriteria evaluation summing a score on several scales for each answer. 
At th end of the test, the student will be catagorized in his dominating category. 
The teacher can use results to create course groups. 
";
$string['usemakegroups_help'] = '## Multitrack test

### Generating moodle groups from results

Using the multitrack test will result in a splitting of the participants among the provided categories.

This setting allows activating the group generation feature that will convert the test result into moodle groups within the course.

Generation can take place even if all participants did not answer the test. By the way, there will not be any generation possible if groups already are set within the course. You will have to delete the existing groups if you want to generate groups again from a later attempt.';

$string['weighted_help'] = '## Multitrack test

### Weighted mode

If you enable this mode, each answer will be associated a weight that will be added to the relevant category if choosen.

This allows creating tests in which each question may not have the same influence upon the final result.

When weighted mode is on, the default weight is 1.0, which is equivalent not to use weighting.';

$string['pagesize_help'] = '## Multitrack test

### Page size

This parameter allows choosing how many questions will be displayed per page. Setting this value to 1 will display a single question page.';

$string['allowreplay_help'] = '## Multitrack test

### Allow test replay

this setting allows disabling or enabling globally the capability to make multiple attempts of the test. Participants will need having the "Make multiple attempts" capability enabled in their profile to get access to the replay button.';

$string['magtest_help'] = ' 
Magtest implements such magazine tests collecting answers "by category" of answers. The test allows you to define some categories and deploy a set of questions that will have as many proposals for each category. 

When a participants plays the test, he will have to choose one of the possible proposals, after what the category score will be increased.

At the end of the test, the winning category has collected the higher score. A conclusion can be shown to users highlighting the winning sentence, and followed eventually by an overal conclusion.

The resulting groups of participants, sorted by the winning category can eventually be converted to Moodle groups. This module can thus be used as a self-discrimating operator when starting a course session, or when splitting a course population into groups becomes necessary.';

$string['you_have_to_create_categories'] = 'You have to create at least one category before you can create any questions.';
$string['question_text'] = 'Question Text';
$string['answer'] = 'Answer';
$string['helpertext'] = 'Helper Text';
$string['updatecategories'] = 'Update Category';
$string['updatequestion'] = 'Update Question';