<?php

    /**
    * Allows managing question set
    * 
    * @package    mod-magtest
    * @category   mod
    * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
    * @contributors   Etienne Roze
    * @contributors   Wafa Adham for version 2
    * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
    * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
    * @see        questions.controller.php for associated controller.
    */

/// check preconditions    

    if (!defined('MOODLE_INTERNAL')){
        die('Internal call only');
    }
    
/// invoke controller
    if ($action){
        require 'questions.controller.php';
    }

    $nb_cat = $DB->count_records_select('magtest_category', 'magtestid = '.$magtest->id.' AND name <> \'\'');
    if ( $nb_cat < 2) {
        echo $OUTPUT->notification(get_string('youneedcreatingcategories','magtest'));
        return; // give control back to view.php
    }

    $categories = magtest_get_categories($magtest->id);
	$categorycount = count($categories);    
    $questions = magtest_get_questions($magtest->id);    
    $orderstr = get_string('sortorder', 'magtest');
    $questionstr = get_string('question', 'magtest');
    $answersstr = get_string('answerweights', 'magtest');
    $commandstr = get_string('commands', 'magtest');
    
    //prepare the table
    $table = new html_table();
    $table->head = array("<b>$orderstr</b>","<b>$questionstr</b>","<b>$answersstr</b>","<b>$commandstr</b>");
    $table->size = array('5%','50%','30%','15%');
    $table->align = array('left','left','center','right');
    if (!empty($questions)){
        foreach($questions as $question){
            if (empty($question->answers)) $question->answers = array();
            $order = $question->sortorder;
            $commands = '<div class="questioncommands">';
            $commands .= "<a href=\"{$CFG->wwwroot}/mod/magtest/editquestions.php?id={$cm->id}&qid={$question->id}\"><img src=\"".$OUTPUT->pix_url('t/edit')."\"></a>";
            $commands .= "&nbsp;<a id='delete' href=\"{$CFG->wwwroot}/mod/magtest/view.php?id={$cm->id}&amp;view=questions&amp;what=delete&amp;qid={$question->id}\"><img src=\"".$OUTPUT->pix_url('t/delete')."\"></a>";
            if ($question->sortorder > 1) {
                $commands .= "&nbsp;<a href=\"{$CFG->wwwroot}/mod/magtest/view.php?id={$cm->id}&amp;view=questions&amp;what=up&amp;qid={$question->id}\"><img src=\"".$OUTPUT->pix_url('t/up')."\"></a>";
            } else {
                $commands .= '&nbsp;<img src="'.$OUTPUT->pix_url('up_shadow', 'magtest').'">';
            }
            if ($question->sortorder < count($questions)) {
                $commands .= "&nbsp;<a href=\"{$CFG->wwwroot}/mod/magtest/view.php?id={$cm->id}&amp;view=questions&amp;what=down&amp;qid={$question->id}\"><img src=\"".$OUTPUT->pix_url('t/down')."\"></a>";
            } else {
                $commands .= '&nbsp;<img src="'.pix_url('down_shadow', 'magtest').'">';
            }
            $commands .='</div>';
            $validanswercount = 0;
            $weights = array();
            foreach($question->answers as $answer){
            	$weights[] = $categories[$answer->categoryid]->name.': '.$answer->weight;
            }
            
            $answercheck = '('.implode(',<br/> ', $weights).')';
            $question->questiontext = file_rewrite_pluginfile_urls( $question->questiontext, 'pluginfile.php',$context->id, 'mod_magtest', 'question', 0);

<<<<<<< HEAD
            $table->data[] = array($question->sortorder, format_string(format_text($question->questiontext, $question->questiontextformat)), $answercheck, $commands);
=======
if (!empty($questions)) {
    foreach ($questions as $question) {
        if (empty($question->answers)) {
            $question->answers = array();
        }
        $order = $question->sortorder;
        $commands = '<div class="questioncommands">';
        $cmdurl = new moodle_url('/mod/magtest/editquestions.php', array('id' => $cm->id, 'qid' => $question->id));
        $commands .= '<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/edit').'</a>';
        $cmdurl = new moodle_url('/mod/magtest/view.php', array('id' => $cm->id, 'view' => 'questions', 'what' => 'delete', 'qid' => $question->id));
        $commands .= '&nbsp;<a id="delete" href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/delete').'</a>';
        if ($question->sortorder > 1) {
            $cmdurl = new moodle_url('/mod/magtest/view.php', array('id' => $cm->id, 'view' => 'questions', 'what' => 'up', 'qid' => $question->id));
            $commands .= '&nbsp;<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/up').'</a>';
        } else {
            $commands .= '&nbsp;'.$OUTPUT->pix_icon('up_shadow', '', 'magtest').'">';
        }
        if ($question->sortorder < count($questions)) {
            $cmdurl = new moodle_url('/mod/magtest/view.php', array('id' => $cm->id, 'view' => 'questions', 'what' => 'down', 'qid' => $question->id));
            $commands .= '&nbsp;<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/down').'</a>';
        } else {
            $commands .= '&nbsp;'.$OUTPUT->pix_icon('down_shadow', 'magtest');
        }
        $commands .='</div>';
        $validanswercount = 0;
        $weights = array();
        foreach ($question->answers as $answer) {
            $weights[] = $categories[$answer->categoryid]->name.': '.$answer->weight;
>>>>>>> MOODLE_34_STABLE
        }
    }
    print('<center>');
    if (!empty($questions)){
        echo html_writer::table($table);
    }
    else{
        print(get_string('noquestions','mod_magtest')) ;
    }

    $options['id'] = $cm->id;
    $options['qid'] = -1;
 
    echo $OUTPUT->single_button(new moodle_url('editquestions.php', $options), get_string('addquestion', 'magtest'), 'get');
    print('</center>');
