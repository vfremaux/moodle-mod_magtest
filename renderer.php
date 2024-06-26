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

    protected $filtermanager;

    public function __construct() {
        global $PAGE;
        $this->filtermanager = filter_manager::instance();
        parent::__construct($PAGE, null);
    }

    /**
     * Implementation of special help rendering.
     * @param help_icon $helpicon
     * @return string HTML fragment
     */
    public function answer_help_icon($answerid) {
        global $CFG;

        // First get the help image icon.
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

        $data = array('id' => $id, 'url' => $url->out(false));
        $this->page->requires->js_init_call('M.util.help_icon.add', array($data));

        // And finally span.
        return html_writer::tag('span', $output, array('class' => 'helplink'));
    }

	/**
	 * Print a multichoice quiz
	 *
	 */
    public function print_magtest_quiz(&$questions, &$categories, $context, $magtest) {
        global $PAGE;

        $template = new StdClass;

        foreach ($questions as $question) {
            $qtpl = new StdClass;
            $qtpl->qid = $question->id;
            $qt = file_rewrite_pluginfile_urls($question->questiontext, 'pluginfile.php', $context->id,
                                               'mod_magtest', 'question', 0);
            $this->filtermanager = filter_manager::instance();
            $this->filtermanager->setup_page_for_filters($PAGE, $PAGE->context); // Setup global stuff filters may have.
            $qt = $this->filtermanager->filter_string($qt, $PAGE->context);
            if (strpos($qt, '<p>') !== 0) {
                // Add a formatting paragraph around.
                $qt = '<p class="magtest-question-wrapper">'.$qt.'</p>';
            }
            $qtpl->questiontext = $qt;

            $i = 0;
            shuffle($question->answers);

            foreach ($question->answers as $answer) {
                $atpl = new StdClass;
                $atpl->aid = $answer->id;
                if (empty($magtest->hidesymbols)) {
                    $catsymbol = $categories[$answer->categoryid]->symbol;
                    $atpl->symbolurl = magtest_get_symbols_baseurl($magtest).$catsymbol;
                }
                $at = file_rewrite_pluginfile_urls($answer->answertext, 'pluginfile.php', $context->id,
                                                   'mod_magtest', 'questionanswer', $answer->id);
                $this->filtermanager = filter_manager::instance();
                $this->filtermanager->setup_page_for_filters($PAGE, $PAGE->context); // Setup global stuff filters may have.
                $at = $this->filtermanager->filter_string($at, $PAGE->context);
                if (strpos($at, '<p>') !== 0) {
                    // Add a formatting paragraph around.
                    $at = '<p class="magtest-answer-wrapper">'.$at.'</p>';
                }
                $atpl->answertext = $at;
                if (!empty($answer->helper)) {
                    $atpl->hashelper = true;
                    $atpl->helper = $this->answer_help_icon($answer->id);
                }
                $qtpl->answers[] = $atpl;
            }
            $template->questions[] = $qtpl;
        }

        return $this->output->render_from_template('mod_magtest/magtest_quiz', $template);
    }

    public function print_magtest_singlechoice(&$questions, $context) {

        $template = new StdClass;

        foreach ($questions as $question) {
            $qtpl = new StdClass;
            $qtpl->qid = $question->id;
            $qt = file_rewrite_pluginfile_urls($question->questiontext, 'pluginfile.php', $context->id,
                                               'mod_magtest', 'question', 0);
            $this->filtermanager = filter_manager::instance();
            $this->filtermanager->setup_page_for_filters($PAGE, $PAGE->context); // Setup global stuff filters may have.
            $qt = $this->filtermanager->filter_string($qt, $PAGE->context);
            if (strpos($qt, '<p>') !== 0) {
                // Add a formatting paragraph around.
                $qt = '<p class="magtest-question-wrapper">'.$qt.'</p>';
            }
            $qtpl->questiontext = $qt;
            $template->questions[] = $qtpl;
        }

        return $this->output->render_from_template('mod_magtest/magtest_singlechoice', $template);
    }

    /**
     * Effective test form.
     */
    public function make_test(&$magtest, &$cm, &$context, &$nextset, &$categories) {
        global $COURSE;

        $currentpage = optional_param('qpage', 0, PARAM_INT);

        $template = new StdClass;
        $template->cmid = $cm->id;
        $template->magtestid = $magtest->id;
        $template->nextpage = $currentpage + 1;

        if (empty($magtest->singlechoice)) {
            $template->magteststandard = $this->print_magtest_quiz($nextset, $categories, $context, $magtest);
        } else {
            $template->magtestsingle = $this->print_magtest_singlechoice($nextset, $context);
        }

        $template->savehandler = 'if (checkanswers()){document.forms[\'maketest\'].what.value = \'save\'; document.forms[\'maketest\'].submit();} return true;';
        $template->canreplay = false;
        if (!$magtest->endtimeenable || time() < $magtest->endtime) {
            if ($magtest->allowreplay && has_capability('mod/magtest:multipleattempts', $context)) {
                $template->canreplay = true;
                $template->resethandler = 'document.forms[\'maketest\'].what.value = \'reset\'; document.forms[\'maketest\'].submit(); return true;';
            }
        }
        $courseurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
        $template->backhandler = 'document.location.href = \''.$courseurl.'\'; return true;';

        if (!$magtest->singlechoice) {
            $template->label = str_replace("'", "\\'", get_string('pagenotcomplete', 'magtest'));
            $template->nextsetarray = implode(',', array_keys($nextset));
        }
        return $this->output->render_from_template('mod_magtest/test', $template);
    }

    public function categories_preview($categories) {
        
    }

    /**
     * Test preview mode.
     */
    public function preview($questions, $magtest) {
        global $DB, $COURSE, $PAGE;

        $template = new StdClass;

        if (empty($questions)) {
            $template->noquestions = true;
        }

        $template->singlechoice = $magtest->singlechoice;
        $template->weighted = $magtest->weighted;
        $template->questions = [];

        $courseurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
        $template->backhandler = 'document.location.href = \''.$courseurl.'\'; return true;';

        if ($magtest->singlechoice) {
            foreach ($questions as $question) {
                $qtpl = new StdClass;
                $question->questiontext = file_rewrite_pluginfile_urls($question->questiontext, 'pluginfile.php', $context->id,
                                                                       'mod_magtest', 'question', 0);
                $qtpl->questiontext = format_string($question->questiontext);
                $weights = array();
                foreach ($question->answers as $answer) {
                    $weights[] = $answer->weight;
                }
                $qtpl->weights = ' ('.implode(',', $weights).') ';
                $template->questions[] = $qtpl;
            }
        } else {
            foreach ($questions as $question) {
                $qtpl = new StdClass;
                $qt = file_rewrite_pluginfile_urls($question->questiontext, 'pluginfile.php', $context->id,
                                                                       'mod_magtest', 'question', 0);

                $this->filtermanager = filter_manager::instance();
                $this->filtermanager->setup_page_for_filters($PAGE, $PAGE->context); // Setup global stuff filters may have.
                $qt = $this->filtermanager->filter_string($qt, $PAGE->context);
                $qtpl->questiontext = $qt;

                shuffle($question->answers);
                foreach ($question->answers as $answer) {
                    $answertpl = new StdClass;
                    $cat = $DB->get_record('magtest_category', array('id' => $answer->categoryid));
                    if (empty($magtest->hidesymbols)) {
                        $answertpl->symbolurl = magtest_get_symbols_baseurl($magtest).$cat->symbol;
                    }

                    $answer->answertext  = file_rewrite_pluginfile_urls($answer->answertext, 'pluginfile.php', $context->id,
                                                                        'mod_magtest', 'questionanswer', $answer->id);

                    $answertpl->answertext = format_string($answer->answertext);
                    $answertpl->catname = format_string($cat->name);
                    if ($magtest->weighted) {
                        $answertpl->weight = $answer->weight;
                    }
                    if (!empty($answer->helper)) {
                        $answertpl->helper = $this->answer_help_icon($answer->id);
                    }
                    $qtpl->answers[] = $answertpl;
                }
                $template->questions[] = $qtpl;
            }
        }

        return $this->output->render_from_template('mod_magtest/preview', $template);
    }
}
