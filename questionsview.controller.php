<?php
if (!(isset($id) and $view==='questions' and  has_capability('mod/magtest:manage', $context))) {
  print 'You have not to see this page';
  exit;
 }

  /* First save data */


//$question = (object)addslashes_recursive($_POST['question']);
$question = (object)$_POST['question'];

$DB->update_record('magtest_question', $question);


//$tab_answers = addslashes_recursive($question->answers);
$tab_answers = $question->answers;
unset($question->answers);

foreach($tab_answers as $id=>$answer) {

  $DB->update_record('magtest_answer', (object)$answer);
  $question->answers[] = (object)$answer;
}
// Finish to save data. 


switch ($action) {

 case get_string('save'):

//already done 
   break;
 case get_string('addquestion','magtest') :
   $question = magtest_add_empty_question($magtest->id);
   $nb_questions = $nb_questions + 1;
   $first = true;

   break;
 case get_string('delquestion','magtest') : 
   // Does this question answered by a user ? If not we can delete it.
   if (! $DB->record_exists('magtest_useranswer', array('questionid' => $question->id))) {
     $DB->delete_records('magtest_question', array('id' => $question->id));
     $DB->delete_records('magtest_answer', array('questionid' => $question->id));
     // Update the qorder value of all questions after the deleted one
     // I can do that with one sql query but I prefer use php for sql compatibiliy problem
     for ($i = $question->qorder+1; $i <= $nb_questions; $i++) {
       $question2 = get_magtest_question($magtest->id,$i);
       $question2->qorder = $i - 1 ;
       $DB->update_record('magtest_question', $question2);
     }
     $nb_questions = $nb_questions - 1;
     $question = get_magtest_question($magtest->id,$question->qorder - 1);

   }
   break;
 case get_string('<<','magtest'):
   $question2 = get_magtest_question($magtest->id,$question->qorder - 1);
   if ($question2) {
     $question2->qorder = $question->qorder;
     $question->qorder = $question->qorder-1;
     $DB->update_record('magtest_question', $question);
     $DB->update_record('magtest_question', $question2);
   }
   break;
 case get_string('>>','magtest'):
   $question2 = get_magtest_question($magtest->id,$question->qorder + 1);
   if ($question2) {
     $question2->qorder = $question->qorder;
     $question->qorder = $question->qorder+1;
     $DB->update_record('magtest_question', $question);
     $DB->update_record('magtest_question', $question2);
   }

   break;


 default :
   // TODO : verify if $command is a number
   $question = get_magtest_question($magtest->id,$action);
   break;
 }




?>