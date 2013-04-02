<?php

    /**
    * Allows answering to the test, question by question
    * 
    * @package    mod-magtest
    * @category   mod
    * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
    * @contributors   Etienne Roze
    * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
    * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
    * @see        preview.controller.php for associated controller.
    */

    if (!defined('MOODLE_INTERNAL')) {
      	die('You cannot access directly to this page');
    }

    include_once 'renderer.php';
    $MAGTESTOUTPUT = new magtest_renderer();
  
/// run controller
    if ($action){
        require 'maketest.controller.php';
    }

    if ($magtest->starttimeenable && time() <= $magtest->starttime){
        echo '<center>';
        echo $OUTPUT->box(get_string('notopened', 'magtest'), 'errorbox');
        echo '</center>';
        return;
    }

    if (!magtest_test_configuration($magtest)){
        echo '<center>';
        echo $OUTPUT->box(get_string('testnotallok', 'magtest'));
        echo $OUTPUT->continue_button($CFG->wwwroot.'/course/view.php?id='.$course->id);
        echo '</center>';
        return;
    }
    $replay = optional_param('replay', 0, PARAM_BOOL);
    if ($replay && $magtest->allowreplay){
        $DB->delete_records('magtest_useranswer', array('magtestid' => $magtest->id, 'userid' => $USER->id));
    }
    
    $nextset = magtest_get_next_questionset($magtest, $USER->id, $magtest->pagesize);
 
    if ($magtest->pagesize){
        $donerecords = $DB->count_records_select('magtest_useranswer', "magtestid = $magtest->id AND userid = $USER->id ");
        $currentpage = ($donerecords / $magtest->pagesize) + 1;
        $allpages = ceil(($DB->count_records('magtest_question', array('magtestid' => $magtest->id)) / $magtest->pagesize));
    } else {
        $currentpage = 1;
        $allpages = 1; // one unique page of any length
    }

    if (!$nextset) {
        echo $OUTPUT->notification(get_string('testfinish','magtest'));
        include $CFG->dirroot."/mod/magtest/testfinished.php";
        return;
    }

    // Keep this after test finished test, to allow students that have 
    // completed the test to see results.
    if ($magtest->endtimeenable && time() >= $magtest->endtime){
        echo '<center>';
        echo $OUTPUT->box(get_string('closed', 'magtest'), 'errorbox');
        echo '</center>';
        return;
    }
    $categories = magtest_get_categories($magtest->id);
  
    echo $OUTPUT->heading(get_string('answerquestions', 'magtest').format_string($magtest->name).' : '.$currentpage.'/'.$allpages);

    // print a description on first page.
    if (!empty($magtest->description) && $currentpage == 1){
        echo '<br/>';
        echo $OUTPUT->box(format_string($magtest->description));
    }
?>

<form name="maketest" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="view" value="doit" />
<input type="hidden" name="magtestid" value="<?php echo $magtest->id ?>" />
<input type="hidden" name="what" value="" />
<table width="100%" cellspacing="10" cellpadding="10">
<?php
foreach($nextset as $question){
?>
<tr align="top">
  <td width="20%" align="right"><b><?php print_string('question', 'magtest') ?>:</b></td>
  <td align="left" colspan="2">
    <?php echo 
    $question->questiontext = file_rewrite_pluginfile_urls( $question->questiontext, 'pluginfile.php',$context->id, 'mod_magtest', 'question', 0);
    format_string($question->questiontext) 
    
    ?>
  </td>
</tr>
<?php
	$i = 0;
	shuffle($question->answers);
	foreach($question->answers as $answer) {
?>
<tr align="middle">
    <td width="20%" align="right">&nbsp;</td>
    <td align="left" class="magtest-answerline">
        <?php
            $catsymbol = $categories[$answer->categoryid]->symbol;
            $symbolurl = magtest_get_symbols_baseurl($magtest).$catsymbol;
            $symbolimage = "<img class=\"magtest-qsymbol\" src=\"{$symbolurl}\" align=\"bottom\" />&nbsp;&nbsp;";
            echo $symbolimage;
            $answer->answertext  = file_rewrite_pluginfile_urls( $answer->answertext, 'pluginfile.php',$context->id, 'mod_magtest', 'questionanswer', $answer->id);            
            $answertext = preg_replace('/^<p>(.*)<\/p>$/', '\\1', $answer->answertext);
            echo ($answertext).' ';
            if (!empty($answer->helper)){
            	echo $MAGTESTOUTPUT->answer_help_icon($answer->id);
            }
            echo '<br/>';
        ?>
    </td>
    <td class="magtest-answerline">
        <?php 
            echo "<input type=\"radio\" name=\"answer{$question->id}\" value=\"{$answer->id}\" /><br/> ";
        ?>
    </td>
</tr>
<?php
	}
}
?>
<tr align="top">
    <td colspan="3" align="center">
        <input type="button" name="go_btn" value="<?php print_string('save', 'magtest') ?>" onclick="if (checkanswers()){document.forms['maketest'].what.value = 'save'; document.forms['maketest'].submit();} return true;" />
<?php
if (!$magtest->endtimeenable || time() < $magtest->endtime){
    if ($magtest->allowreplay && has_capability('mod/magtest:multipleattempts', $context)){
?>
        <input type="button" name="reset_btn" value="<?php print_string('reset', 'magtest') ?>" onclick="document.forms['maketest'].what.value = 'reset'; document.forms['maketest'].submit(); return true;" />
<?php
    }
}
?>

<input type="button" name="backtocourse_btn" value="<?php print_string('backtocourse', 'magtest') ?>" onclick="document.location.href = '<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id ?>'; return true;" />

    </td>
</tr>
</table>
</form>
<script type="text/javascript">
function checkanswers(){
    var checkids = [<?php echo implode(',', array_keys($nextset)) ?>];
    for(i = 0 ; i < checkids.length ; i++){
        rad_val = '';
        for (var j=0; j < document.forms['maketest'].elements['answer' + checkids[i]].length; j++){
            if (document.forms['maketest'].elements['answer' + checkids[i]][j].checked){
                rad_val = document.forms['maketest'].elements['answer' + checkids[i]].value;
            }
        }
        if (rad_val == ''){
            alert('<?php echo str_replace("'", "\\'", get_string('pagenotcomplete', 'magtest')) ?>');
            return false; 
        }
    }
    return true;
}
</script>