<?php

    /**
    * Allows previewing the test before playing it
    * 
    * @package    mod-magtest
    * @category   mod
    * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
    * @contributors   Etienne Roze
    * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
    * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
    */
    
    if (!defined('MOODLE_INTERNAL')){
      error('You cannot access directly to this page');
    }

    if (!has_capability('mod/magtest:manage', $context)){
        die('You cannot see this page with your role');
     }

/// get questions

    $questions = magtest_get_questions($magtest->id);

    print_heading(get_string('preview', 'magtest'));

/// print viewable question list

    if ($questions ) {
        echo '<ul>';
        foreach($questions as $question) {
            echo '<li>';
            echo format_string($question->questiontext);
            echo '<ul>';
            shuffle($question->answers);
            foreach($question->answers as $answer) {
                $cat = get_record('magtest_category', 'id', $answer->categoryid);
                $imageurl = magtest_get_symbols_baseurl($magtest).$cat->symbol;
                echo "<img class=\"magtest-qsymbol\" src=\"$imageurl\" />&nbsp;&nbsp;";
                echo format_string($answer->answertext).' ('.format_string($cat->name).')';
                if ($magtest->weighted){
                echo ' ['.$answer->weight.'] ';
                }
                echo '<br/>';
            }
            echo '</ul><br />';
            echo '</li>';
        }
        echo '</ul><br />';
    }
?>