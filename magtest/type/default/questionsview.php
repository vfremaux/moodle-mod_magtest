<?php
if (!(isset($id) and $view==='questions' and  has_capability('mod/magtest:manage', $context))) {
  print 'You have not to see this page';
  exit;
 }

$nb_cat = count_records_select('magtest_category','magtestid = '.$magtest->id.' and categoryshortname <> \'\'');
if ( $nb_cat < 2) {
  notify(get_string('you_have_to_create_categories','magtest'));
  exit;
 }

$first = false;

$nb_questions = count_records('magtest_question','magtestid',$magtest->id);

if ($action != ''){
  //    include $CFG->dirroot."/mod/magtest/questionsview.controller.php";
    include "questionsview.controller.php";
    if (! isset($question) or empty($question)) {
      notify('I can\'t get question. Problem !');
      exit;
    }
 } else {
  
  $question = get_magtest_question($magtest->id);
  
  if (! $question ) {
    $question = magtest_add_empty_question($magtest->id);
    $first = true;
  } 
 }

$tab_not_ok = are_questions_not_ok($magtest->id);
if (! $tab_not_ok ) {
  $tab_not_ok = array();
  }

$not_ok =  ( !$first and in_array($question->qorder,$tab_not_ok) );

?>
<form name="editquestions" method="POST" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="view" value="questions" />
<input type="hidden" name="question[id]" value="<?php echo $question->id ?>" />
<input type="hidden" name="question[qorder]" value="<?php echo $question->qorder ?>" />
<table width="100%">

<?php
  print_heading(get_string('editquestions', 'magtest')." $question->qorder");
if ($not_ok) {
  print_heading(get_string('questionneedattention', 'magtest'));
 }
  //<input type="hidden" name="what" value="save" />
?>
  <tr>
  <td align="right"><b><?php print_string('question', 'magtest') ?>:</b></td>
  <td align="left">
  <?php print_textarea(true, 10, 60, 660, 200, 'question[questiontext]', stripslashes($question->questiontext), $COURSE->id); ?>
  </td>
  </tr>
<?php
  $i = 0;
  $categories = get_magtest_categories($magtest->id);
  foreach( $categories as $category) {
     $tab_cat[$category->id] = $category->categoryshortname;
   }
  foreach($question->answers as $answer) {
  $i++;

?>
    <tr>
        <td align="right">
        <input type="hidden" name="question[answers][<?php echo $i ?>][id]" value="<?php echo $answer->id ?>" />
       <b><?php print_string('answer', 'magtest'); echo " $i"; ?>:</b>
        </td>
	<td align="left">
								      <?php print_textarea(true, 10, 60, 660, 200,"question[answers][$i][answertext]",stripslashes($answer->answertext)); ?>
        </td>
    <tr><td>
	</td><td>
	<?php   print_string('choosecategoryforanswer', 'magtest'); ?>

	<?php choose_from_menu($tab_cat,'question[answers]['.$i.'][categoryid]',$answer->categoryid); 
  helpbutton ('choosecategoryforanswer', get_string('choosecategoryforanswer','magtest'), 'magtest'); ?> 
        </td>
    </tr>
<?php
}

?>
    <tr>
        <td colspan="2" align="center">
            <input type="submit" name="what" value="<?php print_string('save') ?>"  />
            <input type="submit" name="what" value="<?php print_string('addquestion', 'magtest') ?>"  />
            <input type="submit" name="what" value="<?php print_string('delquestion', 'magtest') ?>"  />
        </td>
    </tr>
    <tr>
    <td colspan="2" align="center">
<?php


if ($nb_questions > 1) {
  for($i = 1; $i <= $nb_questions; $i++) {
    $str_i = $i;
    if (in_array($i,$tab_not_ok)) {
      $str_i = '<font color = "red">'.$i.'</font>';
    }
    if ($i == $question->qorder ) {
      if ($i > 1) {
	print '<input type="submit" name="what" value="'.get_string('<<', 'magtest').'"  >';
      }
      print $str_i;
      if ($i < $nb_questions) {
	print '<input type="submit" name="what" value="'.get_string('>>', 'magtest').'"  >';
	  }
    } else {
      print '<button type="submit" name="what" value="'.$i.'"  >'.$str_i.'</button>';
      
    }
  }
  helpbutton ('helpnavigationquestion', get_string('helpnavigationquestion','magtest'), 'magtest');
 }

use_html_editor();
?>
    </td></tr>
</table>
</form>