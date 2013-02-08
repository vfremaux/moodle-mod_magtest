<?php 
    // $Id: view.php,v 1.9 2012-11-02 20:30:48 vf Exp $
    /**
    * This page prints a particular instance of NEWMODULE
    * 
    * @author 
    * @version $Id: view.php,v 1.9 2012-11-02 20:30:48 vf Exp $
    * @package magtest
    **/

    require_once ('../../config.php');
    require_once ($CFG->dirroot . '/mod/magtest/lib.php');
    require_once ($CFG->dirroot . '/mod/magtest/locallib.php');

    $id 	= optional_param('id', 0, PARAM_INT);                   // Course Module ID, or
    $a  	= optional_param('a', 0, PARAM_INT);                    // magtest ID
    $view 	= optional_param('view',@$SESSION->view, PARAM_ACTION); // view
    $page 	= optional_param('page',@$SESSION->page, PARAM_ACTION); // page
    $action = optional_param('what', '', PARAM_RAW);                // command

    //load jquery
    $PAGE->requires->js('/mod/magtest/js/jquery-1.8.2.min.js');
    $PAGE->requires->js('/mod/magtest/js/view.js');
    
    $SESSION->view = $view;
    $SESSION->page = $page;
   
    if ($id){
        if (!$cm = get_coursemodule_from_id('magtest', $id)){
            print_error ('invalidcoursemodule');
        }

        if (!$course = $DB->get_record('course', array('id' => $cm->course))){
            print_error ('coursemisconf');
		}

        if (!$magtest = $DB->get_record('magtest', array('id' => $cm->instance))){
            print_error ('invalidcoursemodule');
        }
    } else {
        if (!$magtest = $DB->get_record('magtest', array('id' => $a))){
            print_error ('invalidcoursemodule');
		}

        if (!$course = $DB->get_record('course', array('id' => $magtest->course))){
            print_error ('coursemisconf');
        }

        if (!$cm = get_coursemodule_from_instance('magtest', $magtest->id, $course->id)){
            print_error ('invalidcoursemodule');
        }
    }

    require_course_login($course, true, $cm);
    /* 
    if (debugging()){
        echo "MVC[$view:$page:$action]";
    } */

    add_to_log($course->id, 'magtest', 'view', 'view.php?id=$cm->id', '$magtest->id', $cm->id);
    $context    = context_module::instance($cm->id);

    /// Print the page header

    $strmagtests = get_string('modulenameplural', 'magtest');
    $strmagtest  = get_string('modulename', 'magtest');

    /// Guest trap    

    if (isguestuser()){
        print_error('guestcannotuse', 'magtest', '', $CFG->wwwroot.'/course/view.php?id='.$course->id);
        exit;
    }

    /// print tabs

    if (!preg_match("/doit|preview|categories|questions|results|stat/", $view)){
        $view = 'doit';
    }

    if (has_capability('mod/magtest:doit', $context)){
        $tabname	= get_string('doit', 'magtest');
        $row[]		= new tabobject('doit', "view.php?id={$cm->id}&amp;view=doit", $tabname);
    }

    if (has_capability('mod/magtest:manage', $context)){
        $tabname	= get_string('preview', 'magtest');
        $row[]  	= new tabobject('preview', "view.php?id={$cm->id}&amp;view=preview", $tabname);
        $tabname	= get_string('categories', 'magtest');
        $row[]  	= new tabobject('categories', "view.php?id={$cm->id}&amp;view=categories", $tabname);
        $tabname	= get_string('questions', 'magtest');
        $row[]  	= new tabobject('questions', "view.php?id={$cm->id}&amp;view=questions", $tabname);
    }

    if (has_capability('mod/magtest:viewotherresults', $context)){
        $tabname = get_string('results', 'magtest');
        $row[]   = new tabobject('results', "view.php?id={$cm->id}&amp;view=results", $tabname);
    }

    if (has_capability('mod/magtest:viewgeneralstat', $context)){
        $tabname = get_string('stat', 'magtest');
        $row[]   = new tabobject('stat', "view.php?id={$cm->id}&amp;view=stat", $tabname);
    }

    $tabrows[] = $row;

    if ($view == 'results'){
        if (!preg_match("/byusers|bycats/", $page)){
            $page = 'bycats';
        }

        $tabname     = get_string('resultsbyusers', 'magtest');
        $tabrows[1][]= new tabobject('byusers', "view.php?id={$cm->id}&amp;view=results&amp;page=byusers", $tabname);
        $tabname     = get_string('resultsbycats', 'magtest');
        $tabrows[1][]= new tabobject('bycats', "view.php?id={$cm->id}&amp;view=results&amp;page=bycats", $tabname);

        if (!empty($page)){
            $selected = $page;
            $activated = array($view);
        }
    } else {
        $selected = $view;
        $activated = '';
    }

    /// Print the main part of the page

    switch ($view){
        case 'doit':
            if (!has_capability('mod/magtest:doit', $context)){
                print_error('errornotallowed', 'magtest');
            }
            $file_to_include = 'maketest.php';
            break;
        case 'preview':
            if (!has_capability('mod/magtest:manage', $context)){
                redirect ("view.php?view=doit&amp;id={$cm->id}");
            }
            $file_to_include = 'preview.php';
            break;
        case 'categories':
            if (!has_capability('mod/magtest:manage', $context)){
                redirect ("view.php?view=doit&amp;id={$cm->id}");
            }
            $file_to_include = 'categories.php';
            break;
        case 'questions':
            if (!has_capability('mod/magtest:manage', $context)){
                redirect ("view.php?view=doit&amp;id={$cm->id}");
            }
            $file_to_include = "questions.php";
            break;
        case 'results':
            if (!has_capability('mod/magtest:viewotherresults', $context)){
                redirect ("view.php?view=doit&amp;id={$cm->id}");
            }
            switch ($page){
                case 'byusers':
                    $file_to_include = "resultsbyusers.php";
                    break;
                case 'bycats': $file_to_include = "resultsbycats.php";
            }
            break;
        case 'stat':
            if (!has_capability('mod/magtest:viewgeneralstat', $context)){
                redirect ("view.php?view=doit&amp;id={$cm->id}");
			}
            $file_to_include = "stat.php";
            break;
        default:;
    }

    /// start printing the whole view
    
    $PAGE->set_title("$course->shortname: $magtest->name");
    $PAGE->set_heading("$course->fullname");
    /* SCANMSG: may be additional work required for $navigation variable */
    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(true);
    $PAGE->set_url($CFG->wwwroot . '/mod/magtest/view.php?id=' . $id);
    $PAGE->set_button($OUTPUT->update_module_button($cm->id, 'magtest'));
    $PAGE->set_headingmenu(navmenu($course, $cm));
    echo $OUTPUT->header();
    echo $OUTPUT->container_start('mod-header');
    print_tabs($tabrows, $selected, '', $activated);
    echo '<br/>';
    echo $OUTPUT->container_end();
        
    include $file_to_include;

    /// Finish the page
    echo '<br/>';
    echo $OUTPUT->footer($course);
?>