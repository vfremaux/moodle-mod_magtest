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
 * Allows managing question set
 *
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @contributors   Etienne Roze
 * @contributors   Wafa Adham for version 2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @see        questions.controller.php for associated controller.
 */
defined('MOODLE_INTERNAL') || die();

if ($action) {
    require($CFG->dirroot.'/mod/magtest/questions.controller.php');
}

$nbcat = $DB->count_records_select('magtest_category', 'magtestid = '.$magtest->id.' AND name <> \'\'');
if ($nbcat < 2) {
    echo $OUTPUT->notification(get_string('youneedcreatingcategories', 'magtest'));
    return; // Give control back to view.php.
}

$categories = magtest_get_categories($magtest->id);
$categorycount = count($categories);
$questions = magtest_get_questions($magtest->id);
$orderstr = get_string('sortorder', 'magtest');
$questionstr = get_string('question', 'magtest');
$answersstr = get_string('answerweights', 'magtest');
$commandstr = get_string('commands', 'magtest');

// Prepare the table.
$table = new html_table();
$table->head = array("<b>$orderstr</b>", "<b>$questionstr</b>", "<b>$answersstr</b>", "<b>$commandstr</b>");
$table->size = array('5%', '50%', '30%', '15%');
$table->align = array('left', 'left', 'center', 'right');

if (!empty($questions)) {
    foreach ($questions as $question) {
        if (empty($question->answers)) {
            $question->answers = array();
        }
        $order = $question->sortorder;
        $commands = '<div class="questioncommands">';
        $cmdurl = new moodle_url('/mod/magtest/editquestions.php', array('id' => $cm->id, 'qid' => $question->id));
        $commands .= '<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/edit').'</a>';
<<<<<<< HEAD
        $cmdurl = new moodle_url('/mod/magtest/view.php', array('id' => $cm->id, 'view' => 'questions', 'what' => 'delete', 'qid' => $question->id));
        $commands .= '&nbsp;<a id="delete" href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/delete').'</a>';
        if ($question->sortorder > 1) {
            $cmdurl = new moodle_url('/mod/magtest/view.php', array('id' => $cm->id, 'view' => 'questions', 'what' => 'up', 'qid' => $question->id));
=======
        $params = array('id' => $cm->id, 'view' => 'questions', 'what' => 'delete', 'qid' => $question->id);
        $cmdurl = new moodle_url('/mod/magtest/view.php', $params);
        $commands .= '&nbsp;<a id="delete" href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/delete', get_string('delete')).'</a>';
        if ($question->sortorder > 1) {
            $params = array('id' => $cm->id, 'view' => 'questions', 'what' => 'up', 'qid' => $question->id);
            $cmdurl = new moodle_url('/mod/magtest/view.php', $params);
>>>>>>> MOODLE_35_STABLE
            $commands .= '&nbsp;<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/up').'</a>';
        } else {
            $commands .= '&nbsp;'.$OUTPUT->pix_icon('up_shadow', '', 'magtest').'">';
        }
        if ($question->sortorder < count($questions)) {
<<<<<<< HEAD
            $cmdurl = new moodle_url('/mod/magtest/view.php', array('id' => $cm->id, 'view' => 'questions', 'what' => 'down', 'qid' => $question->id));
=======
            $params = array('id' => $cm->id, 'view' => 'questions', 'what' => 'down', 'qid' => $question->id);
            $cmdurl = new moodle_url('/mod/magtest/view.php', $params);
>>>>>>> MOODLE_35_STABLE
            $commands .= '&nbsp;<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/down').'</a>';
        } else {
            $commands .= '&nbsp;'.$OUTPUT->pix_icon('down_shadow', 'magtest');
        }
        $commands .= '</div>';
        $validanswercount = 0;
        $weights = array();
        foreach ($question->answers as $answer) {
            $weights[] = $categories[$answer->categoryid]->name.': '.$answer->weight;
        }

        $answercheck = '('.implode(',<br/> ', $weights).')';
        $question->questiontext = file_rewrite_pluginfile_urls($question->questiontext, 'pluginfile.php', $context->id,
                                                               'mod_magtest', 'question', 0);

        $qt = format_string(format_text($question->questiontext, $question->questiontextformat));
        $table->data[] = array($question->sortorder, $qt, $answercheck, $commands);
    }
}
echo '<center>';

if (!empty($questions)) {
    echo html_writer::table($table);
} else {
    echo get_string('noquestions', 'mod_magtest');
}

$options['id'] = $cm->id;
$options['qid'] = -1;

echo $OUTPUT->single_button(new moodle_url('editquestions.php', $options), get_string('addquestion', 'magtest'), 'get');
echo '</center>';
