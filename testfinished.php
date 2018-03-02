<?php
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

/**
 * Prints results of the test for the user
 *
 * @package    mod-magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

if (!defined('MOODLE_INTERNAL')) {
    die('You cannot access directly to this page');
}

$useranswers = magtest_get_useranswers($magtest->id, $USER->id);

if (!$useranswers) {
    echo '<center>';
    echo $OUTPUT->notification(get_string('nouseranswer','magtest'));
    echo '<br/>';
    if (!$magtest->endtimeenable || time() < $magtest->endtime) {
        if ($magtest->allowreplay && has_capability('mod/magtest:multipleattempts', $context)) {
            $options['id'] = $cm->id;
            $options['view'] = 'doit';
            $options['what'] = 'reset';
            echo $OUTPUT->single_button(new moodle_url('view.php', $options), get_string('reset', 'magtest'), 'get');
        }
    }
    $options = array();
    $options['id'] = $course->id;
    echo $OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/course/view.php', $options), get_string('backtocourse', 'magtest'), 'get');
    echo '</center>';
    echo $OUTPUT->footer($COURSE);
    exit;
}

$categories = magtest_get_categories($magtest->id);
$questions = magtest_get_questions($magtest->id);

// Prepare information relative to the categories in the final table.

foreach($categories as $cat) {
    $tab[$cat->id] = 0;
}

// Accumulation of the nb of answer in each category.

foreach ($useranswers as $useranswer) {

    if ($magtest->singlechoice) {
        $question = $questions[$useranswer->questionid];
        foreach ($question->answers as $answer) {
            if ($useranswer->answerid) {
                $catid = $categories[$answer->categoryid]->id;
                $tab[$catid] = 0 + @$tab[$catid] + $answer->weight;
            }
        }
    } else {
        $question = $questions[$useranswer->questionid];
        $answer = $question->answers[$useranswer->answerid];
        $catid = $categories[$answer->categoryid]->id;
        $tab[$catid] = 0 + @$tab[$catid] + $answer->weight;
    }
}
$maxearned = max($tab);

echo '<center>';

echo $OUTPUT->heading($OUTPUT->user_picture($USER).fullname($USER));
$categorystr = get_string('category', 'magtest');
$scorestr = get_string('score', 'magtest');
$descstr = get_string('descresult', 'magtest');
$table = new html_table();
$table->head = array("<b>$descstr</b>", "<b>$scorestr</b>");
$table->align = array('left', 'center');
$table->width = '90%';
$table->size = array('70%', '30%');
foreach ($categories as $cat) {
    if ($tab[$cat->id] == $maxearned) {
        $pf = '<span class="winner">';
        $sf = '</span>';
    } else {
        $pf = '<span class="looser">';
        $sf = '</span>';
    }
    $symbolurl = magtest_get_symbols_baseurl($magtest).$cat->symbol;
    $symbolimg = "<img src=\"$symbolurl\" /> ";
    $table->data[] = array($symbolimg.$pf.format_string(format_text($cat->description, @$cat->format)).'<br/>'.format_string(format_text($cat->result, @$cat->format)).$sf, $pf.$tab[$cat->id].$sf);
}
echo html_writer::table($table);

if (!empty($magtest->result)) {
    echo '<br/>';
    echo $OUTPUT->box(format_string($magtest->result));
}

if ($magtest->allowreplay && has_capability('mod/magtest:multipleattempts', $context)) {
    if (!$magtest->endtimeenable || time() < $magtest->endtime) {
        $options['id'] = $cm->id;
        $options['view'] = 'doit';
        $options['what'] = 'reset';
        echo $OUTPUT->single_button(new moodle_url('view.php', $options), get_string('reset', 'magtest'), 'get');
    } else {
        echo $OUTPUT->box(get_string('closedtestadvice', 'magtest'));
    }
}
echo '</center>';
