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
 * Controller for maketest
 *
 * @package    mod-magtest
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @contributors   Etienne Roze
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * @see        resultsbycats.php for view.
 *
 * @usecase    makegroups
 */

/* ********************************* make moodle groups from results ********************** */

if ($action == 'setprofile') {
    magtest_compile_results($magtest, $users, $categories, $maxcat);

    foreach ($categories as $category) {
        if (!empty($category->users)) {
            foreach ($category->users as $userid) {
                $DB->set_field('user_info_data', '');
            }
        }
    }
}

if ($action == 'makegroups') {
    $groupmode = groupmode($course, $cm);
    if ($groupmode == NOGROUPS || $magtest->usemakegroups) {
        $fields = mod_magtest\compat::get_user_fields('u');
        $users = get_users_by_capability($context, 'mod/magtest:doit', $fields, 'lastname');
    } else {
        print_error('errorbadgroupmode', 'magtest');
    }

    magtest_compile_results($magtest, $users, $categories, $maxcat);

    foreach ($categories as $category) {
        $now = time();
        $group = new StdClass;
        $group->courseid = $course->id;
        $group->name = (empty($category->outputgroupname)) ? $category->name : $category->outputgroupname;
        $group->description = $category->outputgroupdesc;
        $group->enrolmentkey = '';
        $group->timecreated = $now;
        $group->timemodified = $now;
        if (!$groupid = $DB->insert_record('groups', $group)) {
            print_error('errorcreatinggroup', 'magtest');
        }
        if (!empty($category->users)) {
            foreach ($category->users as $userid) {
                $groupmember = new StdClass;
                $groupmember->groupid = $groupid;
                $groupmember->userid = $userid;
                $groupmember->timeadded = $now;
                if (!$DB->insert_record('groups_members', $groupmember)) {
                    print_error('errorgroupmembership', 'magtest');
                }
            }
        }
    }
    redirect(new moodle_url('/group/index.php', array('id' => $course->id)));
}
