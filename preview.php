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
if (!defined('MOODLE_INTERNAL')) {
  	die('You cannot access directly to this page');
}

if (!has_capability('mod/magtest:manage', $context)) {
    die('You cannot see this page with your role');
}

include_once 'renderer.php';
$MAGTESTOUTPUT = new magtest_renderer();

echo $OUTPUT->heading(get_string('preview', 'magtest'));

// Categories.

echo $OUTPUT->heading(get_string('categories', 'magtest'), 3);
$categories = magtest_get_categories($magtest->id);

// Get questions.

echo $OUTPUT->heading(get_string('questions', 'magtest'), 3);
$questions = magtest_get_questions($magtest->id);

// Print viewable question list.

if ($questions) {
	if ($magtest->singlechoice) {
        echo '<ul>';
        foreach ($questions as $question) {
            echo '<li>';
            $question->questiontext = file_rewrite_pluginfile_urls( $question->questiontext, 'pluginfile.php',$context->id, 'mod_magtest', 'question', 0);

            echo format_string($question->questiontext);
            $weights = array();
            foreach ($question->answers as $answer) {
            	$weights[] = $answer->weight;
            }
            echo ' ('.implode(',', $weights).') ';
        }
        echo '</ul>';
	} else {
        echo '<ul>';
        foreach ($questions as $question) {
            echo '<li>';
            $question->questiontext = file_rewrite_pluginfile_urls( $question->questiontext, 'pluginfile.php',$context->id, 'mod_magtest', 'question', 0);

            echo format_string($question->questiontext);
            echo '<ul>';
            shuffle($question->answers);
            foreach ($question->answers as $answer) {
                $cat = $DB->get_record('magtest_category', array('id' => $answer->categoryid));
                $imageurl = magtest_get_symbols_baseurl($magtest).$cat->symbol;
                
                $answer->answertext  = file_rewrite_pluginfile_urls( $answer->answertext, 'pluginfile.php',$context->id, 'mod_magtest', 'questionanswer', $answer->id);

                echo "<img class=\"magtest-qsymbol\" src=\"$imageurl\" />&nbsp;&nbsp;";
                echo ($answer->answertext).' ('.format_string($cat->name).')';
                if ($magtest->weighted) {
                	echo ' ['.$answer->weight.'] ';
                }
                if (!empty($answer->helper)) {
                	echo $MAGTESTOUTPUT->answer_help_icon($answer->id);
                }
                echo '<br/>';
            }
            echo '</ul><br />';
            echo '</li>';
        }
        echo '</ul><br />';
    }
} else {
	echo $OUTPUT->box(get_string('noquestions', 'magtest'), 'notification');
}
