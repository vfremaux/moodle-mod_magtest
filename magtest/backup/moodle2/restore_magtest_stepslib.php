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
 * @package mod-magtest
 * @copyright 2010 onwards Valery Fremaux (valery.freamux@club-internet.fr)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_url_activity_task
 */

/**
 * Structure step to restore one magtest activity
 */
class restore_magtest_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $magtest = new restore_path_element('magtest', '/activity/magtest');
        $paths[] = $magtest;
        $categories = new restore_path_element('magtest_category', '/activity/magtest/categories/category');
        $paths[] = $categories;
        $questions = new restore_path_element('magtest_question', '/activity/magtest/questions/question');
        $paths[] = $questions;
        $answers = new restore_path_element('magtest_answer', '/activity/magtest/answers/answer');
        $paths[] = $answers;
        
        if ($userinfo){
	        $paths[] = new restore_path_element('magtest_useranswer', '/activity/magtest/useranswers/useranswer');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_magtest($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);

        // insert the label record
        $newitemid = $DB->insert_record('magtest', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function after_execute() {
        // Add magtest related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_magtest', 'intro', null);
        $this->add_related_files('mod_magtest_answer', 'answertext', null);
        $this->add_related_files('mod_magtest_question', 'questiontext', null);
        $this->add_related_files('mod_magtest_category', 'description', null);
    }

    protected function process_magtest_category($data) {
    	global $DB;
    	
        $data = (object)$data;
        $oldid = $data->id;

        $data->magtestid = $this->get_new_parentid('magtest');

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newitemid = $DB->insert_record('magtest_category', $data);
        $this->set_mapping('magtest_category', $oldid, $newitemid, false); // Has no related files
    }

    protected function process_magtest_answer($data) {
    	global $DB;
    	
        $data = (object)$data;
        $oldid = $data->id;

        $data->magtestid = $this->get_new_parentid('magtest');
        $data->questionid = $this->get_mappingid('magtest_question', $data->questionid);
        $data->categoryid = $this->get_mappingid('magtest_category', $data->categoryid);

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newitemid = $DB->insert_record('magtest_answer', $data);
        $this->set_mapping('magtest_answer', $oldid, $newitemid, false); // Has no related files
    }

    protected function process_magtest_question($data) {
    	global $DB;
    	
        $data = (object)$data;
        $oldid = $data->id;

        $data->magtestid = $this->get_new_parentid('magtest');

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newitemid = $DB->insert_record('magtest_question', $data);
        $this->set_mapping('magtest_question', $oldid, $newitemid, false); // Has no related files
    }

    protected function process_magtest_useranswer($data) {
    	global $DB;
    	
        $data = (object)$data;
        $oldid = $data->id;

        $data->magtestid = $this->get_new_parentid('magtest');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->questionid = $this->get_mappingid('magtest_question', $data->questionid);
        $data->answerid = $this->get_mappingid('magtest_answer', $data->answerid);

        // The data is actually inserted into the database later in inform_new_usage_id.
        $newitemid = $DB->insert_record('magtest_useranswer', $data);
        $this->set_mapping('magtest_useranswer', $oldid, $newitemid, false); // Has no related files
    }

}
