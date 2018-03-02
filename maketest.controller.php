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

/* ****************************************** save answers ********************** */

if ($action == 'save') {
    if ($magtest->singlechoice) {
        // On single choice, just record selected questions without answers.
        $qids = required_param_array('qids', PARAM_INT);
        $qidslist = implode("','", $qids);
        $select = " id IN ('$qidslist') AND magtestid = ? ";
        $DB->delete_records_select('magtest_useranswer', $select, array($magtest->id));

        $inputs = optional_param_array('answers', array(), PARAM_INT);
        foreach ($qids as $qid) {
            $useranswer = new StdClass();
            $useranswer->magtestid = $magtest->id;
            $useranswer->userid = $USER->id;
            if (in_array($qid, $inputs)) {
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
                $useranswer->magtestid = $magtest->id;
                $useranswer->userid = $USER->id;
                $useranswer->answerid = required_param($akey, PARAM_INT);
                $useranswer->questionid = $questionid;
                $useranswer->timeanswered = time();
                if ($old = $DB->get_record('magtest_useranswer', array('userid' => $USER->id, 'magtestid' => $magtest->id, 'questionid' => $questionid))) {
                    $useranswer->id = $old->id;
                    $DB->update_record('magtest_useranswer', $useranswer);
                } else {
                    $DB->insert_record('magtest_useranswer', $useranswer);
                }
            }
        }
    }
}

/* ****************************************** reset ********************** */

if ($action == 'reset') {
    if ($magtest->allowreplay and has_capability('mod/magtest:multipleattempts', $context)) { // Protect again here.
        $DB->delete_records('magtest_useranswer', array('magtestid' => $magtest->id, 'userid' => $USER->id));
    }
}

