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
 * @package    mod
 * @subpackage tracker
 * @copyright  2010 onwards Valery Fremaux {valery.fremaux@club-internet.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_vodclic_activity_task
 */

/**
 * Define the complete label structure for backup, with file and id annotations
 */
class backup_magtest_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $magtest = new backup_nested_element('magtest', array('id'), array(
			'name', 'intro', 'introformat', 'starttime', 'starttimeenable', 'endtime', 'endtimeenable', 'timecreated', 'result', 'weighted', 'usemakegroups', 'pagesize', 'allowreplay'));

        $answers = new backup_nested_element('answers');

        $answer = new backup_nested_element('answer', array('id'), array(
            'questionid', 'answertext', 'answertextformat', 'helper', 'helperformat', 'categoryid', 'weight'));

        $questions = new backup_nested_element('questions');

        $question = new backup_nested_element('question', array('id'), array(
           'questiontext', 'questiontextformat', 'sortorder' ));

        $useranswers = new backup_nested_element('useranswers');

        $useranswer = new backup_nested_element('useranswer', array('id'), array(
            'answerid', 'userid', 'questionid', 'timeanswered'));
            
        $categories = new backup_nested_element('categories');
        
        $category = new backup_nested_element('category', array('id'), array(
			'name', 'descriptionformat', 'description', 'result', 'sortorder', 'symbol', 'outputgroupname', 'outputgroupdesc'));
            
        // Build the tree
        // (love this)
        $magtest->add_child($categories);
        $categories->add_child($category);

        $magtest->add_child($questions);
        $questions->add_child($question);

        $magtest->add_child($answers);
        $answers->add_child($answer);

		$magtest->add_child($useranswers);
		$useranswers->add_child($useranswer);

        // Define sources
        $magtest->set_source_table('magtest', array('id' => backup::VAR_ACTIVITYID));
        $category->set_source_table('magtest_category', array('magtestid' => backup::VAR_PARENTID));
        $answer->set_source_table('magtest_answer', array('magtestid' => backup::VAR_PARENTID));
        $question->set_source_table('magtest_question', array('magtestid' => backup::VAR_PARENTID));

        if ($userinfo) {
            $useranswer->set_source_table('magtest_useranswer', array('magtestid' => backup::VAR_PARENTID));
        }

        // Define id annotations
        // (none)
        $useranswer->annotate_ids('user', 'userid');

        // Define file annotations
        $magtest->annotate_files('mod_magtest', 'intro', null); // This file area hasn't itemid
        $magtest->annotate_files('mod_magtest', 'result', null); // This file area hasn't itemid
        $answer->annotate_files('mod_magtest', 'answertext', 'id'); // This file area has itemid
        $category->annotate_files('mod_magtest', 'result', 'id'); // This file area has itemid
        $question->annotate_files('mod_magtest', 'questiontext', 'id'); // This file area has itemid

        // Return the root element (tracker), wrapped into standard activity structure
        return $this->prepare_activity_structure($magtest);
    }
}
