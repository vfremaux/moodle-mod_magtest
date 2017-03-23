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
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/magtest/locallib.php');

/**
 * Form to add or update categories
 *
 */
class Category_Form extends moodleform {

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
        global $CFG, $catid, $DB, $id;

        $mform = $this->_form;

        $mform->addElement('header', 'header0', get_string($this->cmd.'categories', 'magtest'));

        $mform->addElement('hidden', 'catid');
        $mform->setType('catid', PARAM_INT);

        $mform->addElement('hidden', 'howmany');
        $mform->setType('howmany', PARAM_INT);
        $mform->setDefault('howmany', $this->howmany);

        $mform->addElement('hidden', 'what');
        $mform->setDefault('what', 'do'.$this->cmd);
        $mform->setType('what', PARAM_TEXT);

        $mod_context = context_module::instance($id);
        $fileoptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' =>  $mod_context);

        if ($this->cmd == 'add') {
            $mform->addElement('hidden', 'cmd', 'add');
            $mform->setType('cmd', PARAM_TEXT);

            $categories = magtest_get_categories($this->magtest->id);
            $categoryids = array_keys($categories);
            for ($i = 0; $i < $this->howmany; $i++) {
                $num = $i + 1;

                $mform->addElement('static', 'header_'.$num, '<h2>'.get_string('category', 'magtest')." ".$num.'</h2>');

                $mform->addElement('text', 'catname_'.$num, get_string('name'), '', array('size' => '120', 'maxlength' => '255'));
                $mform->setType('catname_'.$num, PARAM_CLEANHTML);

                $symboloptions = magtest_get_symbols($magtest, $renderingpathbase);

                $mform->addElement('selectgroups','catsymbol_'.$num, get_string('symbol', 'mod_magtest'), $symboloptions);

                $catdesc_editor = $mform->addElement('editor', 'catdescription_'.$num, get_string('description'), null, $fileoptions);
                $label = get_string('categoryresult', 'magtest');
                $catresult_editor  = $mform->addElement('editor', 'catresult_'.$num, $label, null, $fileoptions);

                if ($this->magtest->usemakegroups) {
                    $attrs = array('size' => '128', 'maxlength' => '255');
                    $mform->addElement('text', 'outputgroupname_'.$num, get_string('outputgroupname', 'magtest'), '', $attrs);
                    $mform->setType('outputgroupname_'.$num, PARAM_CLEANHTML);
                    $attrs = array('size' => '255', 'maxlength' => '255');
                    $mform->addElement('text', 'outputgroupdesc_'.$num, get_string('outputgroupdesc', 'magtest'), '', $attrs);
                    $mform->setType('outputgroupdesc_'.$num, PARAM_CLEANHTML);
                }
            }
        } else if ($this->cmd == 'update') {

            $mform->addElement('hidden', 'cmd', 'update');
            $mform->setType('cmd', PARAM_ALPHA);

            // Load current cat.
            $category = $DB->get_record('magtest_category', array('id' => $catid));
            if (empty($category)) {
                print_error('errorinvalidcategory', 'magtest');
            }

            $mform->addElement('hidden', 'catid', $category->id);
            $mform->setType('catid', PARAM_INT);

            $mform->addElement('static', 'header', '<h2>'.get_string('category', 'magtest').'</h2>');

            $attrs = array('size' => '120', 'maxlength' => '255');
            $mform->addElement('text', 'catname', get_string('name'), '', $attrs);
            $mform->setDefault('catname', $category->name);
            $mform->setType('catname', PARAM_CLEANHTML);

            $symboloptions = magtest_get_symbols($magtest, $renderingpathbase);

            $selectgroup = $mform->addElement('selectgroups', 'symbol', get_string('symbol', 'mod_magtest'), $symboloptions);
            $selectgroup->setValue($category->symbol);

            $catdesc_editor = $mform->addElement('editor', 'catdescription', get_string('description'), null, $fileoptions);
            $catdesc_editor->setValue(array('text' => $category->description));

            $catresult_editor  = $mform->addElement('editor', 'catresult', get_string('categoryresult', 'magtest'), null, $fileoptions);
            $catresult_editor->setValue(array('text' => $category->result));

            if ($this->magtest->usemakegroups) {
                $attrs = array('size' => '128', 'maxlength' => '255');
                $mform->addElement('text', 'outputgroupname', get_string('outputgroupname', 'magtest'), '', $attrs);
                $mform->setType('outputgroupname', PARAM_CLEANHTML);
                $attrs = array('size' => '255', 'maxlength' => '255');
                $mform->addElement('text', 'outputgroupdesc', get_string('outputgroupdesc', 'magtest'), '', $attrs);
                $mform->setType('outputgroupdesc', PARAM_CLEANHTML);
            }
        }

        $this->add_action_buttons();
    }
}
