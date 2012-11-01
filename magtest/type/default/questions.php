<?php

    /**
    * Allows managing question set
    * 
    * @package    mod-magtest
    * @category   mod
    * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
    * @contributors   Etienne Roze
    * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
    * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
    * @see        questions.controller.php for associated controller.
    */

/// check preconditions    

    if (!defined('MOODLE_INTERNAL')){
        error('Internal call only');
    }
    
    $nb_cat = count_records_select('magtest_category','magtestid = '.$magtest->id.' AND name <> \'\'');
    if ( $nb_cat < 2) {
        notify(get_string('youneedcreatingcategories','magtest'));
        exit;
    }
    
/// invoke controller

    $categorycount = count_records('magtest_category', 'magtestid', $magtest->id);
    $questions = magtest_get_questions($magtest->id);    
    
    $orderstr = get_string('sortorder', 'magtest');
    $questionstr = get_string('question', 'magtest');
    $answersstr = get_string('answercount', 'magtest');
    $commandstr = get_string('commands', 'magtest');

    $table->head = array("<b>$orderstr</b>","<b>$questionstr</b>","<b>$answersstr</b>","<b>$commandstr</b>");
    $table->size = array('5%','40%','40%','15%');
    
    if (!empty($questions)){
        foreach($questions as $question){
            if (empty($question->answers)) $question->answers = array();
            $order = $question->sortorder;
            
            $commands = "<a href=\"{$CFG->wwwroot}/mod/magtest/view.php?id={$cm->id}&amp;view=questions&amp;what=updatequestion&amp;qid={$question->id}\"><img src=\"{$CFG->pixpath}/t/edit.gif\"></a>";
            $commands .= "&nbsp;<a href=\"{$CFG->wwwroot}/mod/magtest/view.php?id={$cm->id}&amp;view=questions&amp;what=delete&amp;qid={$question->id}\"><img src=\"{$CFG->pixpath}/t/delete.gif\"></a>";
            if ($question->sortorder > 1) {
                $commands .= "&nbsp;<a href=\"{$CFG->wwwroot}/mod/magtest/view.php?id={$cm->id}&amp;view=questions&amp;what=up&amp;qid={$question->id}\"><img src=\"{$CFG->pixpath}/t/up.gif\"></a>";
            } else {
                $commands .= "&nbsp;<img src=\"{$CFG->wwwroot}/mod/magtest/pix/up_shadow.gif\">";
            }
            if ($question->sortorder < count($questions)) {
                $commands .= "&nbsp;<a href=\"{$CFG->wwwroot}/mod/magtest/view.php?id={$cm->id}&amp;view=questions&amp;what=down&amp;qid={$question->id}\"><img src=\"{$CFG->pixpath}/t/down.gif\"></a>";
            } else {
                $commands .= "&nbsp;<img src=\"{$CFG->wwwroot}/mod/magtest/pix/down_shadow.gif\">";
            }
    
            $validanswercount = 0;
            foreach($question->answers as $answer){
                if (!empty($answer->answertext)){
                    $validanswercount++;
                }
            }
            $answercheck = ($validanswercount == $categorycount) ? $validanswercount : '<span class="magtest-error">'.$validanswercount.' '.get_string('erroremptyanswers','magtest').'</span>' ;
            $table->data[] = array($question->sortorder, format_string(format_text($question->questiontext, $question->format)), $answercheck, $commands);
        }
    }
?>
<center>
<form name="editquestions" method="POST" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="view" value="questions" />
<?php
    if (!empty($questions)){
        print_table($table);
    }
?>
</form>
<p>
<?php
    $options['id'] = $cm->id;
    $options['what'] = 'addquestion';
    print_single_button('#', $options, get_string('addquestion', 'magtest'));
?></p>
</center>
