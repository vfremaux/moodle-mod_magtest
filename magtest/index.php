<?php // $Id: index.php,v 1.1 2011-09-16 09:23:29 vf Exp $
/**
 * This page lists all the instances of magtest in a particular course
 *
 * @author 
 * @package mod-magtest
 * @category mod
 **/

/// Replace magtest with the name of your module

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($course->id, 'magtest', "view all", "index.php?id=$course->id", "");


/// Get all required strings

    $strmagtests = get_string('modulenameplural', 'magtest');
    $strmagtest  = get_string('modulename', 'magtest');


/// Print the header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }

    print_header("$course->shortname: $strmagtests", "$course->fullname", "$navigation $strmagtests", "", "", true, "", navmenu($course));

/// Get all the appropriate data

    if (! $magtests = get_all_instances_in_course('magtest', $course)) {
        notice("There are no magtests", "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string('name');
    $strweek  = get_string('week');
    $strtopic  = get_string('topic');

    if ($course->format == 'weeks') {
        $table->head  = array ($strweek, $strname);
        $table->align = array ('center', 'left');
    } else if ($course->format == 'topics') {
        $table->head  = array ($strtopic, $strname);
        $table->align = array ('center', 'left', 'left', 'left');
    } else {
        $table->head  = array ($strname);
        $table->align = array ('left', 'left', 'left');
    }

    foreach ($magtests as $magtest) {
        $magtestname = format_string($magtest->name);
        if (!$magtest->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a class=\"dimmed\" href=\"view.php?id=$magtest->coursemodule\">$magtestname</a>";
        } else {
            //Show normal if the mod is visible
            $link = "<a href=\"view.php?id=$magtest->coursemodule\">$magtest->name</a>";
        }

        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($magtest->section, $link);
        } else {
            $table->data[] = array ($link);
        }
    }

    echo "<br />";

    print_table($table);

/// Finish the page

    print_footer($course);

?>
