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

defined('MOODLE_INTERNAL') || die();

if (!(isset($id) and $view === 'questions' && has_capability('mod/magtest:manage', $context))) {
    print 'You have not to see this page';
    die;
}

$nbcat = $DB->count_records_select('magtest_category', 'magtestid = '.$magtest->id.' and categoryshortname <> \'\'');

if ($nbcat < 2) {
    echo $OUTPUT->notification(get_string('you_have_to_create_categories', 'magtest'));
    echo $OUTPUT->footer();
    die();
}

$first = false;
$nbquestions = $DB->count_records('magtest_question', array('magtestid' => $magtest->id));

if ($action != '') {
    include($CFG->dirroot.'/mod/magtest/questionsview.controller.php');
    if (!isset($question) or empty($question)) {
        echo $OUTPUT->notification('I can\'t get question. Problem !');
        echo $OUTPUT->footer();
        die();
    }
 } else {
    $question = get_magtest_question($magtest->id);
    if (! $question ) {
        $question = magtest_add_empty_question($magtest->id);
        $first = true;
    } 
 }

$tabnotok = are_questions_not_ok($magtest->id);
if (! $tabnotok ) {
    $tabnotok = array();
}

$notok = (!$first && in_array($question->qorder, $tabnotok));

?>
<form name="editquestions" method="POST" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="view" value="questions" />
<input type="hidden" name="question[id]" value="<?php echo $question->id ?>" />
<input type="hidden" name="question[qorder]" value="<?php echo $question->qorder ?>" />
<table width="100%">

<?php
echo $OUTPUT->heading(get_string('editquestions', 'magtest')." $question->qorder");
if ($notok) {
    echo $OUTPUT->heading(get_string('questionneedattention', 'magtest'));
}
?>
<tr>
    <td align="right"><b><?php print_string('question', 'magtest') ?>:</b></td>
    <td align="left">
        <?php print_textarea(true, 10, 60, 660, 200, 'question[questiontext]', $question->questiontext, $COURSE->id); ?>
    </td>
</tr>
<?php
$i = 0;
$categories = get_magtest_categories($magtest->id);
foreach ($categories as $category) {
    $tabcat[$category->id] = $category->categoryshortname;
}
foreach ($question->answers as $answer) {
    $i++;
?>
<tr>
    <td align="right">
        <input type="hidden" name="question[answers][<?php echo $i ?>][id]" value="<?php echo $answer->id ?>" />
        <b><?php print_string('answer', 'magtest'); echo " $i"; ?>:</b>
    </td>
    <td align="left">
        <?php print_textarea(true, 10, 60, 660, 200,"question[answers][$i][answertext]", $answer->answertext); ?>
    </td>
</tr>
<tr>
    <td>
    </td>
    <td>
        <?php 
            print_string('choosecategoryforanswer', 'magtest');
            echo html_writer::select($tabcat, 'question[answers]['.$i.'][categoryid]', $answer->categoryid);
            // helpbutton ('choosecategoryforanswer', get_string('choosecategoryforanswer','magtest'), 'magtest');
        ?>
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

if ($nbquestions > 1) {
    for ($i = 1; $i <= $nbquestions; $i++) {
        $stri = $i;
        if (in_array($i, $tabnotok)) {
            $stri = '<font color = "red">'.$i.'</font>';
        }
        if ($i == $question->qorder ) {
            if ($i > 1) {
                echo '<input type="submit" name="what" value="'.get_string('<<', 'magtest').'"  >';
            }
            print $stri;
            if ($i < $nbquestions) {
                echo '<input type="submit" name="what" value="'.get_string('>>', 'magtest').'"  >';
            }
        } else {
            echo '<button type="submit" name="what" value="'.$i.'"  >'.$stri.'</button>';
        }
    }
}
?>
    </td>
</tr>
</table>
</form>