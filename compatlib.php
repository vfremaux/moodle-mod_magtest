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
 * Cross version compatibility
 *
 * @package mod_magtest
 * @author    Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright 2019 onwards Valery Fremaux (http://www.activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

namespace mod_magtest;

use tabobject;

/**
 * Centralizes function that may change among versions
 */
class compat {

	/**
	 * Page initialisation
	 * @param object $cm
	 * @param object $instance
	 */
    public static function init_page($cm, $instance) {
        global $CFG, $PAGE;
        
        if ($CFG->branch >= 400) {
            $PAGE->set_cm($cm);
            $PAGE->set_activity_record($instance);
        }
    }

	/**
	 * User name fields
	 * @param string $prefix
	 */
    public static function get_user_fields($prefix = 'u') {

        global $CFG;

        if ($CFG->branch < 400) {
            return $prefix.'.id,'.get_all_user_name_fields(true, $prefix);
        } else {
            if (!empty($prefix)) {
                $prefix = $prefix.'.';
            }
            $fields = $prefix.'id';
            $morefields = \core_user\fields::for_name()->with_userpic()->excluding('id')->get_required_fields();
            foreach ($morefields as &$f) {
                $f = $prefix.$f;
            }
            $fields .= ','.implode(',', $morefields);
            return $fields;
        }
    }

    /**
     * Legacy nav : keep in screen nav all but magtest setup
     */
    public static function legacy_nav($cm, $context, $view, $page) {
        global $CFG, $OUTPUT;

        if (has_capability('mod/magtest:doit', $context)) {
            $tabname = get_string('doit', 'magtest');
            $row[] = new tabobject('doit', "view.php?id={$cm->id}&amp;view=doit", $tabname);
        }

        if (has_capability('mod/magtest:manage', $context)) {
            $tabname = get_string('preview', 'magtest');
            $row[] = new tabobject('preview', "view.php?id={$cm->id}&amp;view=preview", $tabname);
            if ($CFG->branch < 400) {
                $tabname = get_string('categories', 'magtest');
                $row[] = new tabobject('categories', "view.php?id={$cm->id}&amp;view=categories", $tabname);
                $tabname = get_string('questions', 'magtest');
                $row[] = new tabobject('questions', "view.php?id={$cm->id}&amp;view=questions", $tabname);
                $tabname = get_string('import', 'magtest');
                $row[] = new tabobject('import', $CFG->wwwroot."/mod/magtest/import/import_questions.php?id={$cm->id}", $tabname);
            }
        }

        if (has_capability('mod/magtest:viewotherresults', $context)) {
            $tabname = get_string('results', 'magtest');
            $row[]   = new tabobject('results', "view.php?id={$cm->id}&amp;view=results", $tabname);
        }

        if (has_capability('mod/magtest:viewgeneralstat', $context)) {
            $tabname = get_string('stat', 'magtest');
            $row[]   = new tabobject('stat', "view.php?id={$cm->id}&amp;view=stat", $tabname);
        }

        $tabrows[] = $row;

        if ($view == 'results') {
            if (!preg_match("/byusers|bycats/", $page)) {
                $page = 'bycats';
            }

            $tabname = get_string('resultsbyusers', 'magtest');
            $tabrows[1][] = new tabobject('byusers', "view.php?id={$cm->id}&amp;view=results&amp;page=byusers", $tabname);
            $tabname = get_string('resultsbycats', 'magtest');
            $tabrows[1][] = new tabobject('bycats', "view.php?id={$cm->id}&amp;view=results&amp;page=bycats", $tabname);

            if (!empty($page)) {
                $selected = $page;
                $activated = array($view);
            }
        } else {
            $selected = $view;
            $activated = '';
        }

        $str = $OUTPUT->container_start('mod-header');
        $str .= print_tabs($tabrows, $selected, '', $activated, true);
        $str .=  '<br/>';
        $str .= $OUTPUT->container_end();

        return $str;
    }
}
