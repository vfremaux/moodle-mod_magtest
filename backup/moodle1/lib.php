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
 * Provides support for the conversion of moodle1 backup to the moodle2 format
 * Based off of a template @ http://docs.moodle.org/dev/Backup_1.9_conversion_for_developers
 *
 * @package    mod
 * @subpackage magtest
 * @copyright  2011 Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Tracker conversion handler
 */
class moodle1_mod_magtest_handler extends moodle1_mod_handler {

    /** @var moodle1_file_manager */
    protected $fileman = null;

    /** @var int cmid */
    protected $moduleid = null;

    /**
     * Declare the paths in moodle.xml we are able to convert
     *
     * The method returns list of {@link convert_path} instances.
     * For each path returned, the corresponding conversion method must be
     * defined.
     *
     * Note that the path /MOODLE_BACKUP/COURSE/MODULES/MOD/MAGTEST does not
     * actually exist in the file. The last element with the module name was
     * appended by the moodle1_converter class.
     *
     * @return array of {@link convert_path} instances
     */
    public function get_paths() {
        return array(
            new convert_path(
                'magtest', '/MOODLE_BACKUP/COURSE/MODULES/MOD/MAGTEST',
                array(
                    'renamefields' => array(
                        'description' => 'intro',
                        'format' => 'introformat'
                     ),
                )
            ),
            new convert_path(
                'answers', '/MOODLE_BACKUP/COURSE/MODULES/MOD/MAGTEST/ANSWERS',
                array(
                )
            ),
            new convert_path(
                'answer', '/MOODLE_BACKUP/COURSE/MODULES/MOD/MAGTEST/ANSWERS/ANSWER',
                array(
                    'renamefields' => array(
                        'format' => 'answertextformat'
                    ),
                )
            ),
            new convert_path(
                'questions', '/MOODLE_BACKUP/COURSE/MODULES/MOD/MAGTEST/QUESTIONS',
                array(
                )
            ),
            new convert_path(
                'question', '/MOODLE_BACKUP/COURSE/MODULES/MOD/MAGTEST/QUESTIONS/QUESTION',
                array(
                    'renamefields' => array(
                        'format' => 'questiontextformat'
                    ),
                )
            ),
            new convert_path(
                'categories', '/MOODLE_BACKUP/COURSE/MODULES/MOD/MAGTEST/CATEGORIES',
                array(
                )
            ),
            new convert_path(
                'category', '/MOODLE_BACKUP/COURSE/MODULES/MOD/MAGTEST/CATEGORIES/CATEGORY',
                array(
                    'renamefields' => array(
                        'format' => 'descriptionformat'
                    ),
                )
            ),
        );
    }

    /**
     * This is executed every time we have one /MOODLE_BACKUP/COURSE/MODULES/MOD/TRACKER
     * data available
     */
    public function process_magtest($data) {
        // Get the course module id and context id.
        $instanceid = $data['id'];
        $cminfo     = $this->get_cminfo($instanceid);
        $moduleid   = $cminfo['id'];
        $contextid  = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

        // Get a fresh new file manager for this instance.
        $this->fileman = $this->converter->get_file_manager($contextid, 'mod_magtest');

        // Convert course files embedded into the intro.
        $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;

        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

        // Write magtest.xml.
        $this->open_xml_writer("activities/magtest_{$moduleid}/magtest.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $moduleid,
            'modulename' => 'magtest', 'contextid' => $contextid));

        $this->xmlwriter->begin_tag('magtest', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        return $data;
    }

    /**
     * This is executed when we reach the closing </MOD> tag of our 'forum' path
     */
    public function on_magtest_end() {

        // Finish writing magtest.xml.
        $this->xmlwriter->end_tag('magtest');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

        // Write inforef.xml.
        $this->open_xml_writer("activities/magtest_{$this->moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }

    // Need wait for all elements an elements item collected into memory structure as nesting change structure occurs.
    public function on_answers_start() {
        $this->xmlwriter->begin_tag('answers');
    }

    public function on_answers_end() {
        $this->xmlwriter->end_tag('answers');
    }

    // Process answer in one single write.
    public function process_answer($data) {

        $instanceid = $data['id'];
        $cminfo     = $this->get_cminfo($data['magtestid']);
        $moduleid   = $cminfo['id'];
        $contextid  = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

        // Get a fresh new file manager for this instance.
        $this->fileman = $this->converter->get_file_manager($contextid, 'mod_magtest');

        // Convert course files embedded into the answertext.
        $this->fileman->filearea = 'answertext';
        $this->fileman->itemid   = $data['id'];

        $data['answertext'] = moodle1_converter::migrate_referenced_files($data['answertext'], $this->fileman);

        // Process data.

        $this->write_xml('answer', array('id' => $data['id'],
                                           'magtestid' => $data['magtestid'],
                                           'questionid' => $data['questionid'],
                                           'answertext' => $data['answertext'],
                                           'answertextformat' => $data['answertextformat'],
                                           'helper' => $data['helper'],
                                           'helperformat' => $data['helperformat'],
                                           'categoryid' => $data['categoryid'],
                                           'weight' => $data['weight']
                                           ));
    }

    public function on_questions_start() {
        $this->xmlwriter->begin_tag('questions');
    }

    public function on_questions_end() {
        $this->xmlwriter->end_tag('questions');
    }

    // Process magtest question in one single write.
    public function process_question($data) {

        $instanceid = $data['id'];
        $cminfo     = $this->get_cminfo($data['magtestid']);
        $moduleid   = $cminfo['id'];
        $contextid  = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

        // Get a fresh new file manager for this instance.
        $this->fileman = $this->converter->get_file_manager($contextid, 'mod_magtest');

        // Convert course files embedded into the questiontext.
        $this->fileman->filearea = 'questiontext';
        $this->fileman->itemid   = $data['id'];

        $data['questiontext'] = moodle1_converter::migrate_referenced_files($data['questiontext'], $this->fileman);

        $this->write_xml('question', array('id' => $data['id'],
                                           'magtestid' => $data['magtestid'],
                                           'questiontext' => $data['questiontext'],
                                           'questionextformat' => $data['questiontextformat'],
                                           'sortorder' => $data['sortorder']
                                           ));

    }

    public function on_categories_start() {
        $this->xmlwriter->begin_tag('categories');
    }

    public function on_categories_end() {
        $this->xmlwriter->end_tag('categories');
    }

    // Process usedelement in one single write.
    public function process_category($data) {

        $cminfo     = $this->get_cminfo($data['magtestid']);
        $moduleid   = $cminfo['id'];
        $contextid  = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

        // Get a fresh new file manager for this instance.
        $this->fileman = $this->converter->get_file_manager($contextid, 'mod_magtest');

        // Convert course files embedded into the category.
        $this->fileman->filearea = 'category';
        $this->fileman->itemid   = $data['id'];

        $data['description'] = moodle1_converter::migrate_referenced_files($data['description'], $this->fileman);

        $this->write_xml('category', array('id' => $data['id'],
                                           'magtestid' => $data['magtestid'],
                                           'name' => $data['name'],
                                           'description' => $data['description'],
                                           'descriptionformat' => $data['descriptionformat'],
                                           'result' => $data['result'],
                                           'outputgroupname' => $data['outputgroupname'],
                                           'outputgroupdesc' => $data['outputgroupdesc'],
                                           'sortorder' => $data['sortorder'],
                                           'symbol' => $data['symbol']
                                           ));
    }
}
