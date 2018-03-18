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
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/magtest/locallib.php');

/**
 * Form to add or update categories
 *
 */

class Question_Form extends moodleform {

    protected $cmd;
    protected $magtest;
    protected $howmany;

    public function __construct(&$magtest, $cmd, $howmany, $action) {
        $this->cmd = $cmd;
        $this->magtest = $magtest;
        $this->howmany = $howmany;
        parent::__construct($action);
    }

    public function definition() {
        global $DB, $CFG, $cm, $qid, $id;

        $mform = $this->_form;
        $modcontext = context_module::instance($id);

        $mform->addElement('header', 'header0', get_string($this->cmd.'question', 'magtest'));

        $mform->addElement('hidden', 'qid');
        $mform->setType('qid', PARAM_INT);

        $mform->addElement('hidden', 'howmany');
        $mform->setType('howmany', PARAM_INT);
        $mform->setDefault('howmany', $this->howmany);

        $mform->addElement('hidden', 'what');
        $mform->setDefault('what', 'do'. $this->cmd);
        $mform->setType('what', PARAM_ALPHA);

        $maxbytes = 1024 * 1024 * 100;
        $questionoptions = array('trusttext' => true,
                                 'subdirs' => false,
                                 'maxfiles' => 100,
                                 'maxbytes' => $maxbytes,
                                 'context' => $modcontext);
        $fileoptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $modcontext);

        if (!empty($qid) && $qid != -1) {
            $question = magtest_get_question($qid);
            $answers = array();

            foreach ($question->answers as $ans) {
                $answers[$ans->categoryid] = $ans->answertext;
            }
        }

        $mform->addElement('hidden', 'magtestid', $this->magtest->id);
        $mform->setType('magtestid', PARAM_INT);

        if ($this->cmd == 'add') {
            $mform->addElement('hidden', 'cmd', 'add');
            $mform->setType('cmd', PARAM_ALPHA);

            $mform->addElement('editor', 'questiontext_editor', get_string('question_text', 'magtest'), null, $fileoptions);
            // Insert hte question.
            // Get the categories.
            $cats = $DB->get_records('magtest_category', array('magtestid' => $this->magtest->id));

            $i = 1;
            foreach ($cats as $cat) {
                $mform->addElement('header', 'header'.$cat->id, get_string('answer', 'magtest', $i));
                $mform->setExpanded('header'.$cat->id);

                $mform->addElement('hidden', 'cats['. $cat->id.']', $cat->id);
                $mform->setType('cats['. $cat->id.']', PARAM_INT);

                $questionanswertext = get_string('category', 'mod_magtest')." '".$cat->name."' answer";
                if (empty($this->magtest->singlechoice)) {
                    $key = 'questionanswer'.$cat->id.'_editor';
                    $questioneditor = $mform->addElement('editor', $key, $questionanswertext, null, $fileoptions);
                    $mform->addRule('questionanswer'.$cat->id.'_editor', null, 'required', null, 'client');
                } else {
                    $label = get_string('singlechoicemode', 'magtest');
                    $mform->addElement('static', 'questionanswer'.$cat->id, $questionanswertext, $label);
                }

                if ($this->magtest->weighted) {
                    $weight = 1;
                    $mform->addElement('text', 'weight'.$cat->id, get_string('weight', 'magtest'), $weight);
                    $mform->setType('weight'.$cat->id, PARAM_INT);
                }

                if (empty($this->magtest->singlechoice)) {
                    $helpertext = get_string('helpertext', 'mod_magtest', $cat->name);
                    $helpereditor = $mform->addElement('editor', 'helper'.$cat->id.'_editor', $helpertext, null, $fileoptions);
                }

                $i++;
            }
        } else if ($this->cmd == 'update') {
            $mform->addElement('hidden', 'cmd', 'update');
            $mform->setType('cmd', PARAM_ALPHA);

            $mform->addElement('hidden', 'qid', $question->id);
            $mform->setType('gid', PARAM_INT);

            $label = get_string('question_text', 'magtest');
            $questiontexteditor = $mform->addElement('editor', 'questiontext_editor', $label, null, $fileoptions);

            $question = file_prepare_standard_editor($question, 'questiontext', $questionoptions, $modcontext,
                                                     'mod_magtest', 'question', 0);

            // Insert hte question.
            // Get the categories.
            $categories = $DB->get_records('magtest_category', array('magtestid' => $this->magtest->id));
            $mform->addElement('header', 'header1', get_string('answers', 'magtest'));
            foreach ($categories as $cat) {
                $mform->addElement('hidden', 'cats['. $cat->id.']', $cat->id);
                $mform->setType('cats['. $cat->id.']', PARAM_INT);
                $questionanswertext = get_string('category', 'magtest')." '".$cat->name."' answer";

                // Get cat answer if exists.
                $select = ' magtestid = ? and categoryid = ? and questionid = ? ';
                $answer = $DB->get_record_select('magtest_answer', $select, array($this->magtest->id, $cat->id, $question->id));

                if (!$this->magtest->singlechoice) {
                    $key = 'questionanswer'.$cat->id.'_editor';
                    $questioneditor = $mform->addElement('editor', $key, $questionanswertext, null, $fileoptions);
                    $mform->addRule('questionanswer'.$cat->id.'_editor', null, 'required', null, 'client');
                }
                if ($this->magtest->weighted) {
                    $weight = (isset($answer->weight)) ? $answer->weight : 1;
                    $mform->addElement('text', 'weight'.$cat->id, get_string('weightfor', 'magtest', $cat->name), $weight);
                    $mform->setType('weight'.$cat->id, PARAM_INT);
                }

                if (!$this->magtest->singlechoice) {
                    $helpertext = get_string('helpertext', 'mod_magtest', $cat->name);
                    $helpereditor = $mform->addElement('editor', 'helper'.$cat->id.'_editor', $helpertext, null, $fileoptions);
                }

                if (!empty($answer)) {
                    if (!$this->magtest->singlechoice) {
                        $field = 'questionanswer'.$cat->id;
                        $question->{$field} = $answer->answertext;
                        $question->{$field.'format'} = FORMAT_HTML;
                        $question  = file_prepare_standard_editor($question, $field, $questionoptions, $modcontext, 'mod_magtest',
                                                                  'questionanswer', $answer->id);

                        $field = 'helper'.$cat->id;
                        $question->{$field} = $answer->helper;
                        $question->{$field.'format'} = FORMAT_HTML;
                        $question  = file_prepare_standard_editor($question, $field, $questionoptions, $modcontext, 'mod_magtest',
                                                                  'helper', $answer->id);
                    }

                    $field = 'weight'.$cat->id;
                    $question->{$field} = $weight;
                }
            }
            $this->set_data($question);
        }
        $this->add_action_buttons();
    }
}
