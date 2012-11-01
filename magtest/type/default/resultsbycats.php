<?php
    /**
    * Prints results of the test for the user
    * 
    * @package    mod-magtest
    * @category   mod
    * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
    * @contributors   Etienne Roze
    * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
    * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
    */

    /**
    * Include and requires
    */
    require_once($CFG->libdir.'/tablelib.php');

    if (!defined('MOODLE_INTERNAL')) {
        error('You cannot access directly to this page');
    }

    // setup group state regarding the user
    $groupmode = groupmode($course, $cm);
    $changegroupid = optional_param('group', -1, PARAM_INT);              
    $currentgroupid = 0 + get_and_set_current_group($course, $groupmode, $changegroupid);

    if (has_capability('moodle/site:accessallgroups', $context)){
        $groups = get_groups($COURSE->id);
    } else {
        $groups = get_groups($COURSE->id, $USER->id);
    }
    $baseurl = $CFG->wwwroot."/mod/magtest/view.php?id={$cm->id}&amp;view=results";
    if ($groups){
        groups_print_course_menu($COURSE, $baseurl);
    }
    
    
/// get users of the current group who can do the test

    // note that usemakegroups is not compatible with course groups as it is used to generate
    // moodle groups in a course and needs having no groups at start.
    if ($groupmode == NOGROUPS || $magtest->usemakegroups){
        $users = get_users_by_capability($context, 'mod/magtest:doit', 'u.id,firstname,lastname,picture,email', 'lastname');
    } else {
        $users = get_users_by_capability($context, 'mod/magtest:doit', 'u.id,firstname,lastname,picture,email', 'lastname', '', '', $currentgroupid);
    }

    // do not recalculate results if they where already calculated in controller.
    if (empty($categories) && $action != 'makegroups'){    
        magtest_compile_results($magtest, $users, $categories, $max_cat);
    }

/// make table head

    echo '<center>';          
    $table->head[] = '<b>'.get_string('category', 'magtest').'</b>';
    $table->head[] = '<b>'.get_string('results', 'magtest').'</b>';
    $table->size = array('30%', '70%');
    $table->width = '80%';
    
    foreach($categories as $cat) {
        $symbolurl = magtest_get_symbols_baseurl($magtest).$cat->symbol;
        $symbolimg = "<img src=\"$symbolurl\" /> ";
        $scoreboard = '<table width="100%" class="magtest-user-list">';
        if (!empty($cat->users)){
            foreach($cat->users as $userid) {
                if ($groupmode != NOGROUPS && !$magtest->usemakegroups) {
                    // we ensure user is in the currently viewed group
                    if (!in_array($userid, array_keys($users))){
                        continue;
                    }
                }
                $user = get_record('user', 'id', $userid);
                $username = print_user_picture($user, $course->id, true, true, true).fullname($user);
                $score = @$max_cat[$user->id]->score;
                $scoreboard .= "<tr><td>{$username}</td><td align=\"right\">{$score}</td></tr>";
            }
        } else {
            $scoreboard .= '<tr><td>'.get_string('nousersinthisgroup', 'magtest').'</td></tr>';
        }
        $scoreboard .= '</table>';

        $table->data[] = array('<span class="magtest-cat-name">'.$symbolimg.' '.$cat->name.'</span><br/>'.format_string(format_text($cat->description, $cat->format)), $scoreboard);
    }    
    print_table($table);
    
    echo '<br/>';
    
    if ($magtest->usemakegroups){
        $allgroups = groups_get_all_groups($COURSE->id);
        if (empty($allgroups)){
            $options['id'] = $cm->id;
            $options['what'] = 'makegroups';
            print_single_button($CFG->wwwroot.'/mod/magtest/view.php', $options, get_string('makegroups', 'magtest'));
        } else {
            print_box(get_string('nogroupcreationadvice', 'magtest'), 'errorbox');
        }
    }
    
    echo '</center>';          
?>