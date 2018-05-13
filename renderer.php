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
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @contributors   Etienne Roze
 * @contributors   Wafa Adham for version 2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

class mod_magtest_renderer extends plugin_renderer_base {

    public function __construct() {
        global $PAGE;
        parent::__construct($PAGE, null);
    }

    /**
     * Implementation of special help rendering.
     * @param help_icon $helpicon
     * @return string HTML fragment
     */
    public function answer_help_icon($answerid) {
        global $CFG;

<<<<<<< HEAD
        // first get the help image icon
=======
        // First get the help image icon.
>>>>>>> MOODLE_35_STABLE
        $title = get_string('helper', 'magtest');
        $alt = get_string('helper', 'magtest');

        $attributes = array('class' => 'iconhelp');
        $output = $this->pix_icon('help', $alt, 'core', $attributes);

        // Now create the link around it - we need https on loginhttps pages.
        $url = new moodle_url('/mod/magtest/help.php', array('answerid' => $answerid));

        $attributes = array('href' => $url, 'title' => $title);
        $id = html_writer::random_id('helpicon');
        $attributes['id'] = $id;
        $output = html_writer::tag('a', $output, $attributes);

        $daat = array('id' => $id, 'url' => $url->out(false));
        $this->page->requires->js_init_call('M.util.help_icon.add', array($data));

        // And finally span.
        return html_writer::tag('span', $output, array('class' => 'helplink'));
    }

    public function print_magtest_quiz(&$questions, &$categories, $context) {

        $str = '';

        foreach ($questions as $question) {
            $str .= '<tr align="top">';
            $str .= '<td width="20%" align="right"><b>'.get_string('question', 'magtest').':</b></td>';
            $str .= '<td align="left" colspan="2">';
            $qt = file_rewrite_pluginfile_urls($question->questiontext, 'pluginfile.php', $context->id,
                                               'mod_magtest', 'question', 0);
            $str .= $question->questiontext = $qt;
            $question->questiontext = format_string($question->questiontext);

            $str .= '</td>';
            $str .= '</tr>';
            $i = 0;
            shuffle($question->answers);

            foreach ($question->answers as $answer) {
                $str .= '<tr align="middle">';
                $str .= '<td width="20%" align="right">&nbsp;</td>';
                $str .= '<td align="left" class="magtest-answerline">';
                $catsymbol = $categories[$answer->categoryid]->symbol;
                $symbolurl = magtest_get_symbols_baseurl($magtest).$catsymbol;
                $symbolimage = '<img class="magtest-qsymbol" src="'.$symbolurl.'" align="bottom" />&nbsp;&nbsp;';
                $str .= $symbolimage;
                $at = file_rewrite_pluginfile_urls($answer->answertext, 'pluginfile.php', $context->id,
                                                   'mod_magtest', 'questionanswer', $answer->id);
                $answer->answertext  = $at;
                $answertext = preg_replace('/^<p>(.*)<\/p>$/', '\\1', $answer->answertext);
                $str .= ($answertext).' ';
                if (!empty($answer->helper)) {
                    $str .= $this->answer_help_icon($answer->id);
                }
                $str .= '<br/>';
                $str .= '</td>';
                $str .= '<td class="magtest-answerline">';
                $str .= '<input type="radio" name="answer'.$question->id.'" value="'.$answer->id.'" /><br/> ';
                $str .= '</td>';
                $str .= '</tr>';
            }
        }

        return $str;
    }

    public function print_magtest_singlechoice(&$questions, $context) {

        $str = '';

        foreach ($questions as $question) {
            $str .= '<tr align="top">';
            $str .= '<td align="left">';
            $qt = file_rewrite_pluginfile_urls($question->questiontext, 'pluginfile.php', $context->id,
                                               'mod_magtest', 'question', 0)
            $str .= $question->questiontext = $qt;
            $question->questiontext = format_string($question->questiontext);
            $str .= '</td>';
            $i = 0;

            $str .= '<td class="magtest-answerline">';
            $str .= '<input type="checkbox" name="answers[]" value="'.$question->id.'" />';
            $str .= '<input type="hidden" name="qids[]" value="'.$question->id.'" />';
            $str .= '</td>';
            $str .= '</tr>';
        }

        return $str;
    }

    public function make_test(&$magtest, &$cm, &$context, &$nextset, &$categories) {
        global $COURSE;

        $currentpage = optional_param('qpage', 0, PARAM_INT);

        $template = new StdClass;
        $template->cmid = $cm->id;
        $template->magtestid = $magtest->id;
        $template->nextpage = $currentpage + 1;

        if (empty($magtest->singlechoice)) {
            $template->magteststandard = $this->print_magtest_quiz($nextset, $categories, $context);
        } else {
            $template->magtestsingle = $this->print_magtest_singlechoice($nextset, $context);
        }

        $template->savestr = get_string('save', 'magtest');
        $template->savehandler = 'if (checkanswers()){document.forms[\'maketest\'].what.value = \'save\'; document.forms[\'maketest\'].submit();} return true;';
        $template->canreplay = false;
        if (!$magtest->endtimeenable || time() < $magtest->endtime) {
            if ($magtest->allowreplay && has_capability('mod/magtest:multipleattempts', $context)) {
                $template->canreplay = true;
                $template->resetstr = get_string('reset', 'magtest');
                $template->resethandler = 'document.forms[\'maketest\'].what.value = \'reset\'; document.forms[\'maketest\'].submit(); return true;';
            }
        }
        $courseurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
        $template->backtocoursestr = get_string('backtocourse', 'magtest');
        $template->backhandler = 'document.location.href = \''.$courseurl.'\'; return true;';

        if (!$magtest->singlechoice) {
            $template->label = str_replace("'", "\\'", get_string('pagenotcomplete', 'magtest'));
            $template->nextsetarray = implode(',', array_keys($nextset));
        }
        return $this->output->render_from_template('mod_magtest/test', $template);
    }
}
