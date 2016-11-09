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
 * Controller for "maketest"
 *
 * @package    mod_magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @see        maketest.php for view.
 *
 * @usecase    save
 * @usecase    reset
 */
namespace mod_magtest;

defined('MOODLE_INTERNAL') || die();


class maketest_controller {

    protected $magtest;

    protected $data;

    protected $received = false;

    public function __construct($magtest) {
        $this->magtest = $magtest;
    }

    /**
     * Receives all needed parameters from outside for each action case.
     * @param string $cmd the action keyword
     * @param array $data incoming parameters from form when directly available, otherwise the
     * function shoudl get them from request
     */
    public function receive($cmd, $data = null) {

        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'save':
                $data->qids = required_param_array('qids', PARAM_INT);
                $data->inputs = optional_param_array('answers', array(), PARAM_INT);
                break;
            case 'reset':
                break;
        }

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $DB, $USER;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        // Save answers *********************************************************************.

        if ($cmd == 'save') {
            if ($magtest->singlechoice) {
                // On single choice, just record selected questions without answers.

                list($insql, $inparams) = $DB->get_in_or_equal($data->qids);
                $inparams[] = $this->magtest->id;
                $select = " id IN $insql AND magtestid = ? ";
                $DB->delete_records_select('magtest_useranswer', $select, $inparams);

                foreach ($data->qids as $qid) {
                    $useranswer = new StdClass();
                    $useranswer->magtestid = $this->magtest->id;
                    $useranswer->userid = $USER->id;
                    if (in_array($qid, $data->inputs)) {
                        $useranswer->answerid = 1;
                    } else {
                        $useranswer->answerid = 0;
                    }
                    $useranswer->questionid = $qid;
                    $useranswer->timeanswered = time();
                    $DB->insert_record('magtest_useranswer', $useranswer);
                }
            } else {
                $inputkeys = preg_grep("/^answer/", array_keys($_POST));
                foreach ($inputkeys as $akey) {
                    if (preg_match("/^answer(\\d+)/", $akey, $matches)) {
                        $questionid = $matches[1];
                        $useranswer = new StdClass();
                        $useranswer->magtestid = $this->magtest->id;
                        $useranswer->userid = $USER->id;
                        $useranswer->answerid = required_param($akey, PARAM_INT);
                        $useranswer->questionid = $questionid;
                        $useranswer->timeanswered = time();
                        $params = array('userid' => $USER->id, 'magtestid' => $this->magtest->id, 'questionid' => $questionid);
                        if ($old = $DB->get_record('magtest_useranswer', $params)) {
                            $useranswer->id = $old->id;
                            $DB->update_record('magtest_useranswer', $useranswer);
                        } else {
                            $DB->insert_record('magtest_useranswer', $useranswer);
                        }
                    }
                }
            }
        }

        // Reset ************************************************************************************.

        if ($cmd == 'reset') {
            if ($magtest->allowreplay && has_capability('mod/magtest:multipleattempts', $context)) {
                // Protect again here.
                $DB->delete_records('magtest_useranswer', array('magtestid' => $this->magtest->id, 'userid' => $USER->id));
            }
        }
    }
}