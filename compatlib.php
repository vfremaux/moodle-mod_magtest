<?php

namespace mod_magtest;

use tabobject;

class compat {

    public static function init_page($cm, $instance) {
        global $CFG, $PAGE;
        
        if ($CFG->branch >= 400) {
            $PAGE->set_cm($cm);
            $PAGE->set_activity_record($instance);
        }
    }
    
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

    public static function legacy_nav($cm, $context, $view, $page) {
        global $CFG, $OUTPUT;

        if ($CFG->branch < 400) {

            if (has_capability('mod/magtest:doit', $context)) {
                $tabname = get_string('doit', 'magtest');
                $row[] = new tabobject('doit', "view.php?id={$cm->id}&amp;view=doit", $tabname);
            }

            if (has_capability('mod/magtest:manage', $context)) {
                $tabname = get_string('preview', 'magtest');
                $row[] = new tabobject('preview', "view.php?id={$cm->id}&amp;view=preview", $tabname);
                $tabname = get_string('categories', 'magtest');
                $row[] = new tabobject('categories', "view.php?id={$cm->id}&amp;view=categories", $tabname);
                $tabname = get_string('questions', 'magtest');
                $row[] = new tabobject('questions', "view.php?id={$cm->id}&amp;view=questions", $tabname);
                $tabname = get_string('import', 'magtest');
                $row[] = new tabobject('import', $CFG->wwwroot."/mod/magtest/import/import_questions.php?id={$cm->id}", $tabname);
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
            print_tabs($tabrows, $selected, '', $activated, true);
            $str .=  '<br/>';
            $str .= $OUTPUT->container_end();
        }

        return $str;
    }
}

if ($CFG->branch >= 400) {
    /**
     * Standard callback for moodle navigation
     */
    function magtest_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $magtestnode) {

        if (has_capability('mod/magtest:manage', $settingsnav->get_page()->context)) {

            $params = ['id' => $settingsnav->get_page()->cm->id, 'view' => 'preview'];
            $reportlink = new moodle_url("/mod/magtest/view.php", $params);
            $magtestnode->add(get_string('preview', 'magtest'), $reportlink, navigation_node::TYPE_SETTING);

            $params = ['id' => $settingsnav->get_page()->cm->id, 'view' => 'categories'];
            $reportlink = new moodle_url("/mod/magtest/view.php", $params);
            $magtestnode->add(get_string('categories', 'magtest'), $reportlink, navigation_node::TYPE_SETTING);

            $params = ['id' => $settingsnav->get_page()->cm->id, 'view' => 'questions'];
            $reportlink = new moodle_url("/mod/magtest/view.php", $params);
            $magtestnode->add(get_string('questions', 'magtest'), $reportlink, navigation_node::TYPE_SETTING);

            $params = ['id' => $settingsnav->get_page()->cm->id];
            $reportlink = new moodle_url("/mod/magtest/import/import_questions.php", $params);
            $magtestnode->add(get_string('import', 'magtest'), $reportlink, navigation_node::TYPE_SETTING);

            $params = ['id' => $settingsnav->get_page()->cm->id, 'vew' => 'results'];
            $reportlink = new moodle_url("/mod/magtest/view.php", $params);
            $node = $magtestnode->add(get_string('results', 'magtest'), $reportlink, navigation_node::TYPE_SETTING);

            $params = ['id' => $settingsnav->get_page()->cm->id, 'vew' => 'stats'];
            $reportlink = new moodle_url("/mod/magtest/view.php", $params);
            $node = $magtestnode->add(get_string('stat', 'magtest'), $reportlink, navigation_node::TYPE_SETTING);
        }
    }
}