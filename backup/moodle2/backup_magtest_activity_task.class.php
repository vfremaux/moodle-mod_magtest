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
 * @package     mod_magtest
 * @category    mod
 * @copyright   2010 onwards Valery Fremaux {@link }
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/magtest/backup/moodle2/backup_magtest_settingslib.php'); // Because it exists (must).
require_once($CFG->dirroot.'/mod/magtest/backup/moodle2/backup_magtest_stepslib.php'); // Because it exists (must).

/**
 * tracker backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_magtest_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        assert(1);
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step.
        $this->add_step(new backup_magtest_activity_structure_step('magtest_structure', 'magtest.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of magtests.
        $search = "/(".$base."\/mod\/magtest\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@MAGTESTINDEX*$2@$', $content);

        // Link to magtests view by moduleid.
        $search = "/(".$base."\/mod\/magtest\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@MAGTESTVIEWBYID*$2@$', $content);

        return $content;
    }
}
