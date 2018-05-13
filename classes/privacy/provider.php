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

namespace mod_magtest\privacy;

use \core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

class provider implements \core_privacy\local\metadata\provider {

    public static function get_metadata(collection $collection) : collection {

        $fields = [
            'userid' => 'privacy:metadata:magtest_useranswer:userid',
            'magtestid' => 'privacy:metadata:magtest_useranswer:magtestid',
            'answerid' => 'privacy:metadata:magtest_useranswer:answerid',
            'questionid' => 'privacy:metadata:magtest_useranswer:questionid',
            'timeanswered' => 'privacy:metadata:magtest_useranswer:timeanswered',
        ];

        $collection->add_database_table('magtest_useranswer', $fields, 'privacy:metadata:magtest_useranswer');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
  public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        // Fetching flashcard_cards context should be sufficiant to get contexts where user is involved in.
        // It may have NO states if it has no deck cards.

        $sql = "
            SELECT
                c.id
            FROM
                {context} c
            INNER JOIN
                {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN
                {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN
                {magtest} mg ON mg.id = cm.instance
            LEFT JOIN
                {magtest_useranswer} ua ON fc.magtestid = mg.id
            WHERE ua.userid = :userid
        ";
 
        $params = [
            'modname'           => 'magtest',
            'contextlevel'      => CONTEXT_MODULE,
            'userid'  => $userid,
        ];
 
        $contextlist->add_from_sql($sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $ctx) {
            $instance = writer::withcontext($ctx);

            $data = new StdClass;

            $params = array('magtestid' => $ctx->instanceid,
                            'userid' => $user->id);
            $answers = $DB->get_records('magtest_useranswer', $params);

            foreach ($answers as $answer) {
                $testanswer = $DB->get_record('magtest_answer', array('id' => $answer->answerid));
                $testquestion = $DB->get_record('magtest_question', array('id' => $answer->questionid));
                $answer->answertext = $testanswer->answertext;
                $answer->questiontext = $testquestion->questiontext;
                $data->answers[] = $answer;
            }

            $instance->export_data(null, $data);
        }
    }

    public static function delete_data_for_all_users_in_context(deletion_criteria $criteria) {
        global $DB;

        $context = $criteria->get_context();
        if (empty($context)) {
            return;
        }

        $DB->delete_records('magtest_useranswer', ['magtestid' => $context->instanceid]);
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $ctx) {
            $DB->delete_records('magtest_useranswer', ['magtestid' => $ctx->instanceid, 'userid' => $userid]);
        }
    }
}