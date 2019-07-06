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
use \core_privacy\local\metadata\collection;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\transform;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\approved_contextlist;

defined('MOODLE_INTERNAL') || die();

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

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
        $contextlist = new contextlist();

        // Fetching magtest context should be sufficiant to get contexts where user is involved in.

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
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     *
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        // Find users with magtest answers entries.
        $sql = "
            SELECT 
                mua.userid
            FROM
                {magtest_useranswer} mua
            JOIN
                {modules} m
            ON
                m.name = :magtest
            JOIN
                {course_modules} cm
            ON
                cm.instance = mua.magtestid AND
                cm.module = m.id
            JOIN
                {context} ctx
            ON
                ctx.instanceid = cm.id AND
                ctx.contextlevel = :modlevel
            WHERE
                ctx.id = :contextid
        ";
        $params = ['magtest' => 'magtest', 'modlevel' => CONTEXT_MODULE, 'contextid' => $context->id];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $ctx) {
            $magtest = self::export_magtest($ctx, $user);

            $sql = "
                SELECT
                    mq.userid,
                    mq.questiontext,
                    mc.name,
                    mc.description,
                    ma.answertext,
                    ma.description,
                    ma.weight,
                FROM
                    {magtest_useranswer} ua,
                    {magtest_question} mq,
                    {magtest_answer} ma,
                    {magtest_category} mc,
                    {magtest} mg
                WHERE
                
                    mq.id = ua.questionid AND
                    ma.id = ua.answerid AND
                    mc.id = ma.categoryid AND
                    mq.magtestid = ?
            ";

            $answers = $DB->get_records_sql($sql, [$magtest->id]);
            if ($answers) {
                foreach ($answers as $answer) {
                    self::export_magtest_answer($ctx, $user, $answer);
                }
            }
        }
    }

    /**
     * Export one entry in the database activity module (one record in {data_records} table)
     *
     * @param \context $context
     * @param \stdClass $user
     * @param \stdClass $recordobj
     */
    protected static function export_magtest_answer($context, $user, $recordobj) {
        global $DB;

        if (!$recordobj) {
            return;
        }

        $recordobj->userid = transform::user($recordobj->userid);
        $recordobj->timeanswered = transform::datetime($recordobj->timeanswered);

        // Data about the record.
        writer::with_context($context)->export_data([$recordobj->id], (object)$recordobj);
    }

    /**
     * Export basic info about magtest activity module
     *
     * @param \context $context
     * @param \stdClass $user
     */
    protected static function export_magtest($context, $user) {
        global $DB;

        if (!$context) {
            return  null;
        }

        $contextdata = helper::get_context_data($context, $user);
        writer::with_context($context)->export_data([], $contextdata);

        $sql = "
            SELECT
                cm.id,
                ".self::get_fields()."
            FROM
                {context} ctx,
                {course_modules} cm,
                {modules} m,
                {magtest} mg
            WHERE
                cm.module = m.id AND
                m.name = 'magtest' AND
                cm.instance = mg.id AND
                ctx.contextlevel = ? AND
                ctx.instanceid = cm.id AND
                ctx.id = ?
        ";

        $magtest = $DB->get_record_sql($sql, [CONTEXT_MODULE, $context->id]);
        $magtest->starttime = transform::datetime($magtest->starttime);
        $magtest->endtime = transform::datetime($magtest->endtime);
        $magtest->weighted = transform::yesno($magtest->weighted);
        $magtest->allowreplay = transform::yesno($magtest->allowreplay);

        writer::with_context($context)->export_data([], $magtest);

        return $magtest;
    }

    protected static function get_fields() {
        return " mg.name, mg.intro, mg.starttime, mg.endtime, weighted, allowreplay ";
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (empty($context)) {
            return;
        }

        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);

        $DB->delete_records('magtest_useranswer', ['magtestid' => $cm->instance]);
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $ctx) {
            $cm = $DB->get_record('course_modules', ['id' => $ctx->instanceid]);
            $DB->delete_records('magtest_useranswer', ['magtestid' => $cm->instance, 'userid' => $userid]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist    $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);

        foreach ($userlist->get_userids() as $uid) {
            $DB->delete_records('magtest_useranswer', ['magtestid' => $cm->instance, 'userid' => $uid]);
        }

    }
}