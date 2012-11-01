<?php  // $Id: view.php,v 1.2 2012-07-07 16:47:54 vf Exp $
/**
 * This page prints a particular instance of NEWMODULE
 * 
 * @author 
 * @version $Id: view.php,v 1.2 2012-07-07 16:47:54 vf Exp $
 * @package magtest
 **/


    require_once('../../config.php');
    require_once($CFG->dirroot.'/mod/magtest/lib.php');
    require_once($CFG->dirroot.'/mod/magtest/locallib.php');

    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $a  = optional_param('a', 0, PARAM_INT);  // magtest ID
    $view = optional_param('view', @$SESSION->view, PARAM_ACTION);     // view
    $page = optional_param('page', @$SESSION->page, PARAM_ACTION);     // page
    $action = optional_param('what', '', PARAM_RAW);     // command

    $SESSION->view = $view;
    $SESSION->page = $page;

    if ($id) {
        if (! $cm = get_coursemodule_from_id('magtest', $id)) {
            error("Course Module ID was incorrect");
        }
        if (! $cm = get_record('course_modules', 'id', $id)) {
            error('Course Module ID was incorrect');
        }
    
        if (! $course = get_record('course', 'id', $cm->course)) {
            error('Course is misconfigured');
        }
    
        if (! $magtest = get_record('magtest', 'id', $cm->instance)) {
            error('Course module is incorrect');
        }

    } else {
        if (! $magtest = get_record('magtest', 'id', $a)) {
            error('Course module is incorrect');
        }
        if (! $course = get_record('course', 'id', $magtest->course)) {
            error('Course is misconfigured');
        }
        if (! $cm = get_coursemodule_from_instance('magtest', $magtest->id, $course->id)) {
            error('Course Module ID was incorrect');
        }
    }

    require_login($course->id);
    
    /* 
    if (debugging()){
        echo "MVC[$view:$page:$action]";
    } */

    add_to_log($course->id, 'magtest', 'view', 'view.php?id=$cm->id', '$magtest->id', $cm->id);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

/// Print the page header

    $navigation = build_navigation('', $cm);
    $strmagtests = get_string('modulenameplural', 'magtest');
    $strmagtest  = get_string('modulename', 'magtest');


    print_header("$course->shortname: $magtest->name", "$course->fullname",
                 $navigation,
                  '', '', true, update_module_button($cm->id, $course->id, $strmagtest), 
                  navmenu($course, $cm));

/// Guest trap    

    if (isguest()){
        echo '<br/>';
        print_box(get_string('guestcannotuse', 'magtest'));
        print_continue($CFG->wwwroot.'/course/view.php?id='.$course->id);
        print_footer($course);
        exit;
    }


    /// print tabs

    if (! preg_match("/doit|preview|categories|questions|results|stat/", $view)) $view = 'doit';
    if (has_capability('mod/magtest:doit', $context)){
        $tabname = get_string('doit', 'magtest');
        $row[] = new tabobject('doit', "view.php?id={$cm->id}&amp;view=doit", $tabname);
    }
    if (has_capability('mod/magtest:manage', $context)){
        $tabname = get_string('preview', 'magtest');
        $row[] = new tabobject('preview', "view.php?id={$cm->id}&amp;view=preview", $tabname);
    
        $tabname = get_string('categories', 'magtest');
        $row[] = new tabobject('categories', "view.php?id={$cm->id}&amp;view=categories", $tabname);
      
        $tabname = get_string('questions', 'magtest');
        $row[] = new tabobject('questions', "view.php?id={$cm->id}&amp;view=questions", $tabname);
     }
    if (has_capability('mod/magtest:viewotherresults', $context)){
        $tabname = get_string('results', 'magtest');
        $row[] = new tabobject('results', "view.php?id={$cm->id}&amp;view=results", $tabname);
    }
    if (has_capability('mod/magtest:viewgeneralstat', $context)){
        $tabname = get_string('stat', 'magtest');
        $row[] = new tabobject('stat', "view.php?id={$cm->id}&amp;view=stat", $tabname);
    }

    $tabrows[] = $row; 
    
    if ($view == 'results'){
        if (!preg_match("/byusers|bycats/", $page)) $page = 'bycats';    
        $tabname = get_string('resultsbyusers', 'magtest');
        $tabrows[1][] = new tabobject('byusers', "view.php?id={$cm->id}&amp;view=results&amp;page=byusers", $tabname);
        $tabname = get_string('resultsbycats', 'magtest');
        $tabrows[1][] = new tabobject('bycats', "view.php?id={$cm->id}&amp;view=results&amp;page=bycats", $tabname);

        if (!empty($page)){
            $selected = $page;
            $activated = array($view);
        }
    } else {
        $selected = $view;
        $activated = '';
    }
       
    print_container_start(true, 'mod-header');
    print_tabs($tabrows, $selected, '', $activated);
    echo '<br/>';
    print_container_end();

    if (! isset($magtest->type) or $magtest->type == 'default' ) {
        $include = 'type/default/';
    } else {
        $include = 'type/'.$magtest.'/';
    }
    
    
/// Print the main part of the page

    $controller = ''; 

    switch ($view){
        case 'doit' :
            if (!has_capability('mod/magtest:doit', $context)){
                print_error('errornotallowed', 'magtest');
            }
            if ($action){
                $controller = 'maketest.controller.php';
            }
	        $file_to_include = 'maketest.php';
            break;
        case 'preview' : 
            if (!has_capability('mod/magtest:manage', $context)){
                redirect("view.php?view=doit&amp;id={$cm->id}");
            }
	        $file_to_include = 'preview.php';
	        break;
        case 'categories' :
	        if (!has_capability('mod/magtest:manage', $context)){
                redirect("view.php?view=doit&amp;id={$cm->id}");
            }
            if ($action){
                $controller = 'categories.controller.php';
            }
            $file_to_include = 'categories.php';
            break;
        case 'questions' :
            if (!has_capability('mod/magtest:manage', $context)){
                redirect("view.php?view=doit&amp;id={$cm->id}");
            }
            if ($action){
                $controller = 'questions.controller.php';
            }
            $file_to_include = "questions.php";
            break;
        case 'results' :
            if (!has_capability('mod/magtest:viewotherresults', $context)){
                redirect("view.php?view=doit&amp;id={$cm->id}");
            } 
            if ($action){
                $controller = 'results.controller.php';
            }
            switch ($page) {
                case 'byusers' : 
                    $file_to_include = "resultsbyusers.php";
                    break;
                case 'bycats' : 
                    $file_to_include = "resultsbycats.php";
            }
            break;

        case 'stat' :
            if (!has_capability('mod/magtest:viewgeneralstat', $context)){
                redirect("view.php?view=doit&amp;id={$cm->id}");
            } 
            $file_to_include = "stat.php";
            break;

        default :
	        notify('Nothing to do !! ');
	        print_footer($course);
	        exit;
    }

/// if a controller has to be called, call it

    if ($controller){
        if (file_exists($include.'/'.$controller)) { 
            $res = include $include.'/'.$controller;
        } else {
            $res = include 'type/default/'.$controller;
        }
        if ($res == -1){
            use_html_editor();
            print_footer();
            exit;
        }
    }

/// make the view

    if (file_exists($include.'/'.$file_to_include)) { 
        include $include.'/'.$file_to_include;
    } else {
        include 'type/default/'.$file_to_include;
    }

/// Finish the page
	echo '<br/>';
    print_footer($course);

?>
