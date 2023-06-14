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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @see        categories.controller.php for associated controller.
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class magtest_categories_form extends moodleform {

    public function __construct($magtest) {
        parent::__construct(null, (array)$magtest);
    }

    public function definition() {

        $magtestid = $this->_customdata['id'];
    }

    public function definition_after_data() {
        global $DB;

        $magtestid = $this->_customdata['id'];
        $mform = &$this->_form;

        if (isset($mform->_submitValues['submitbutton'])) {
            if (isset($mform->_submitValues['category'])) {
                $cats = $mform->_submitValues['category'];

                if (!empty($cats)) {
                    foreach ($cats as $category) {
                        $DB->update_record('magtest_category', (object)$category);
                    }
                }
            }

            if (!strcmp($mform->_submitValues['submitbutton'], get_string('add_category', 'magtest'))) {
                $newcategory->categorytext = '';
                $newcategory->magtestid = $magtestid;
                $DB->insert_record('magtest_category', $newcategory);
            } else if (!strcmp($mform->_submitValues['submitbutton'], get_string('delcategory', 'magtest'))) {
                $select = "
                    categorytext = '' AND
                    categoryshortname = '' AND
                    magtestid = ?
                ";
                $maxcat = $DB->get_record_select('magtest_category', $select, array($magtestid), 'max(id) as m ');

                if (isset($maxcat->m)) {
                    // Does a question exist in this category ? If not we can delete it.
                    if (!$DB->record_exists('magtest_question', array('categoryid' => $maxcat->m))) {
                        $DB->delete_records('magtest_category', array('�magtestid' => $magtestid, 'id' => $maxcat->m));
                    }
                }
            }
        }

        $categories = get_magtest_categories($magtestid);

        $mform->addElement('header', 'general', get_string('categories', 'magtest'));
        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'view', 'categories');

        $i = 0;

        if (!empty($categories)) {
            foreach ($categories as $category) {
                $i++;
                $mform->addElement('hidden', 'category['.$category->id.'][id]', $category->id);
                $key = 'category['.$category->id.'][categoryshortname]';
                $mform->addElement('text', $key, get_string('categoryshortname', 'magtest')." $i", array('size' => '10'));
                $mform->setType($key, PARAM_CLEANHTML);
                $mform->setDefault($key, $category->categoryshortname);

                $key = 'category['.$category->id.'][categorytext]';
                $mform->addElement('text', $key, get_string('category', 'magtest')." $i", array('size' => '64'));
                $mform->setType($key, PARAM_CLEANHTML);
                $mform->setDefault($key, $category->categorytext);
            }

            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('save'));
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('delcategory', 'magtest'));
        } else {
            $mform->addElement('static', 'message', get_string('nocategories', 'magtest'));
        }

        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('add_category', 'magtest'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
    }
}
