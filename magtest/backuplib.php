<?PHP //$Id: backuplib.php,v 1.1 2011-09-16 09:23:27 vf Exp $

    /**
    * This php script contains all the stuff to backup/restore
    * magtest mods
    *
    * @package mod-magtest
    * @category mod
    * @author Valery Fremaux (admin@ethnoinformatique.fr)
    * 
    */

    //This is the "graphical" structure of the magtest mod:
    //
    //           magtest                                  
    //          (CL,pk->id)               
    //               |
    //               |                       
    //               +---------------------------------------+
    //               |                                       |
    //         magtest_question                        magtest_category 
    //  (IL, pk->id, fk->magtestid)              (IL, pk->id, fk->magtestid)
    //               |                                       |
    //               +---------------------------------------+
    //                                  |
    //                            magtest_answer
    //                 (IL, pk->id, fk->magtestid*, fk->questionid, fk->categoryid)
    //                                  |
    //                            magtest_useranswer
    //    (UL, pk->id, fk->magtestid*, fk->answerid, fk->userid, fk->questionid*)
    //
    // (*) Redundancies for query optimisation.
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          IL->instance level info
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files
    //
    //-----------------------------------------------------------

    function magtest_backup_mods($bf, $preferences) {
        global $CFG;

        $status = true;

        //Iterate over magtest table
        $magtests = get_records ('magtest', 'course', $preferences->backup_course, 'id');
        if ($magtests) {
            foreach ($magtests as $magtest) {
                $status = $status && magtest_backup_one_mod($bf, $preferences, $magtest);
            }
        }
        return $status;
    }

    function magtest_backup_one_mod($bf, $preferences, $magtest) {
        global $CFG;
        
        if (is_numeric($magtest)) {
            $magtest = get_record('magtest', 'id', $magtest);
        }

        $status = true;

        fwrite ($bf, start_tag('MOD', 3, true));
        //Print choice data
        fwrite ($bf,full_tag('ID', 4, false, $magtest->id));
        fwrite ($bf,full_tag('MODTYPE', 4, false, 'magtest'));
        fwrite ($bf,full_tag('NAME', 4, false, $magtest->name));
        fwrite ($bf,full_tag('DESCRIPTION', 4, false, $magtest->description));
        fwrite ($bf,full_tag('TYPE', 4, false, $magtest->type));
        fwrite ($bf,full_tag('STARTTIME', 4, false, $magtest->starttime));
        fwrite ($bf,full_tag('STARTTIMEENABLE', 4, false, $magtest->starttimeenable));
        fwrite ($bf,full_tag('ENDTIME', 4, false, $magtest->endtime));
        fwrite ($bf,full_tag('ENDTIMEENABLE', 4, false, $magtest->endtimeenable));
        fwrite ($bf,full_tag('TIMECREATED', 4, false, $magtest->timecreated));
        fwrite ($bf,full_tag('RESULT', 4, false, $magtest->result));
        fwrite ($bf,full_tag('WEIGHTED', 4, false, $magtest->weighted));
        fwrite ($bf,full_tag('USEMAKEGROUPS', 4, false, $magtest->usemakegroups));
        fwrite ($bf,full_tag('PAGESIZE', 4, false, $magtest->pagesize));
        fwrite ($bf,full_tag('ALLOWREPLAY', 4, false, $magtest->allowreplay));

        $status = $status && backup_magtest_categories($bf, $preferences, $magtest);
        $status = $status && backup_magtest_questions($bf, $preferences, $magtest);
        $status = $status && backup_magtest_answers($bf, $preferences, $magtest);

        if ($preferences->mods['magtest']->userinfo) {
            $status = $status && backup_magtest_useranswers($bf, $preferences, $magtest);
        }

        /// End mod
        $status = fwrite ($bf, end_tag('MOD', 3, true));
        
        return $status;
    }

    /**
    * Backup magtest categories (executed from magtest_backup_mods)
    */
    function backup_magtest_categories($bf, $preferences, &$magtest) {
        global $CFG;

        $status = true;

        /// Write start tag
        $status = $status && fwrite ($bf, start_tag('CATEGORIES', 4, true));

        $categories = get_records('magtest_category', 'magtestid', $magtest->id);
        /// If there is a category
        if ($categories) {
            /// Iterate over each category of the magtest
            foreach ($categories as $category) {
                /// Start category
                $status = $status && fwrite ($bf, start_tag('CATEGORY', 5, true));
                /// Print category data
                fwrite ($bf, full_tag('ID', 6, false, $category->id));
                fwrite ($bf, full_tag('MAGTESTID', 6, false, $category->magtestid));
                fwrite ($bf, full_tag('NAME', 6, false, $category->name));
                fwrite ($bf, full_tag('FORMAT', 6, false, $category->format));
                fwrite ($bf, full_tag('DESCRIPTION', 6, false, $category->description));
                fwrite ($bf, full_tag('RESULT', 6, false, $category->result));
                fwrite ($bf, full_tag('SORTORDER', 6, false, $category->sortorder));
                fwrite ($bf, full_tag('SYMBOL', 6, false, $category->symbol));
                /// End category
                $status = $status && fwrite ($bf, end_tag('CATEGORY', 5, true));
            }
        }

        /// Write end tag
        $status = $status && fwrite($bf, end_tag('CATEGORIES', 4, true));
        return $status;
    }

    /**
    * Backup magtest_questions (executed from magtest_backup_mods)
    */
    function backup_magtest_questions($bf, $preferences, &$magtest) {
        global $CFG;

        $status = true;

        /// Write start tag
        $status = $status && fwrite ($bf, start_tag('QUESTIONS', 4, true));

        $questions = get_records('magtest_question', 'magtestid', $magtest->id);
        /// If there are questions
        if ($questions) {
            /// Iterate over each question
            foreach ($questions as $question) {
                /// Start question
                $status = $status && fwrite ($bf, start_tag('QUESTION', 5, true));
                /// Print question data
                fwrite ($bf, full_tag('ID', 6, false, $question->id));
                fwrite ($bf, full_tag('MAGTESTID', 6, false, $question->magtestid));
                fwrite ($bf, full_tag('QUESTIONTEXT', 6, false, $question->questiontext));
                fwrite ($bf, full_tag('FORMAT', 6, false, $question->format));
                fwrite ($bf, full_tag('SORTORDER', 6, false, $question->sortorder));
                /// End question
                $status = $status && fwrite ($bf, end_tag('QUESTION', 5, true));
            }
        }

        /// Write end tag
        $status = $status && fwrite($bf, end_tag('QUESTIONS', 4, true));
        return $status;
    }

    /**
    * Backup magtest_answers (executed from magtest_backup_mods)
    */
    function backup_magtest_answers($bf, $preferences, &$magtest) {
        global $CFG;

        $status = true;

        /// Write start tag
        $status = $status && fwrite ($bf, start_tag('ANSWERS', 4, true));

        $answers = get_records('magtest_answer', 'magtestid', $magtest->id);
        /// If there are answers
        if ($answers) {
            /// Iterate over each answer
            foreach ($answers as $answer) {
                /// Start answer
                $status = $status && fwrite ($bf, start_tag('ANSWER', 5, true));
                /// Print answer data
                fwrite ($bf, full_tag('ID', 6, false, $answer->id));
                fwrite ($bf, full_tag('MAGTESTID', 6, false, $answer->magtestid));
                fwrite ($bf, full_tag('QUESTIONID', 6, false, $answer->questionid));
                fwrite ($bf, full_tag('ANSWERTEXT', 6, false, $answer->answertext));
                fwrite ($bf, full_tag('FORMAT', 6, false, $answer->format));
                fwrite ($bf, full_tag('HELPER', 6, false, $answer->helper));
                fwrite ($bf, full_tag('HELPERFORMAT', 6, false, $answer->helperformat));
                fwrite ($bf, full_tag('CATEGORYID', 6, false, $answer->categoryid));
                fwrite ($bf, full_tag('WEIGHT', 6, false, $answer->weight));
                /// End answer
                $status = $status && fwrite ($bf, end_tag('ANSWER', 5, true));
            }
        }

        /// Write end tag
        $status = $status && fwrite($bf, end_tag('ANSWERS', 4, true));
        return $status;
    }

    /**
    * Backup magtest_answers (executed from magtest_backup_mods)
    */
    function backup_magtest_useranswers($bf, $preferences, &$magtest) {
        global $CFG;

        $status = true;

        /// Write start tag
        $status = $status && fwrite ($bf, start_tag('USERANSWERS', 4, true));

        $userchoices = get_records('magtest_useranswer', 'magtestid', $magtest->id);
        /// If there are user choices
        if ($userchoices) {
            /// Iterate over each choice
            foreach ($userchoices as $userchoice) {
                /// Start user choice
                $status = $status && fwrite ($bf, start_tag('USERCHOICE', 5, true));
                /// Print choice data
                fwrite ($bf, full_tag('ID', 6, false, $userchoice->id));
                fwrite ($bf, full_tag('MAGTESTID', 6, false, $userchoice->magtestid));
                fwrite ($bf, full_tag('ANSWERID', 6, false, $userchoice->answerid));
                fwrite ($bf, full_tag('USERID', 6, false, $userchoice->usrid));
                fwrite ($bf, full_tag('QUESTIONID', 6, false, $userchoice->questionid));
                fwrite ($bf, full_tag('TIMEANSWERED', 6, false, $userchoice->timeanswered));
                /// End user choice
                $status = $status && fwrite ($bf, end_tag('USERCHOICE', 5, true));
            }
        }

        /// Write end tag
        $status = $status && fwrite($bf, end_tag('USERANSWERS', 4, true));
        return $status;
    }

   /// Return an array of info (name,value)
   function magtest_check_backup_mods($course, $user_data = false, $backup_unique_code) {

        // First the course data
        $info[0][0] = get_string('modulenameplural', 'magtest');
        if ($ids = magtest_ids($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        $info[1][0] = get_string('categories', 'magtest');
        if ($ids = magtest_category_ids($course)) {
            $info[1][1] = count($ids);
        } else {
            $info[1][1] = 0;
        }

        $info[2][0] = get_string('questions', 'magtest');
        if ($ids = magtest_question_ids($course)) {
            $info[2][1] = count($ids);
        } else {
            $info[2][1] = 0;
        }

        $info[3][0] = get_string('answers', 'magtest');
        if ($ids = magtest_answer_ids($course)) {
            $info[3][1] = count($ids);
        } else {
            $info[3][1] = 0;
        }

        if ($user_data){
            $info[4][0] = get_string('userchoices', 'magtest');
            if ($ids = magtest_useranswer_ids($course)) {
                $info[4][1] = count($ids);
            } else {
                $info[4][1] = 0;
            }
        }

        return $info;
    }

    // Returns an array of magtest id
    function magtest_ids ($course) {

        return get_records('magtest', 'course', $course, '', 'id,course');
    }

    // Returns an array of magtest card id in deck
    function magtest_category_ids($course) {
        global $CFG;

        $query = "
            SELECT 
                mc.id, 
                m.course
            FROM 
                {$CFG->prefix}magtest m,
                {$CFG->prefix}magtest_category mc
            WHERE
                m.id = mc.magtestid AND 
                m.course = '{$course}'
        ";
        return get_records_sql($query);
    }

    // Returns an array of magtest question ids
    function magtest_question_ids($course) {
        global $CFG;

        $query = "
            SELECT 
                mq.id, 
                m.course
            FROM 
                {$CFG->prefix}magtest m,
                {$CFG->prefix}magtest_question mq
            WHERE
                m.id = mq.magtestid AND 
                m.course = '{$course}'
        ";
        return get_records_sql($query);
    }

    // Returns an array of magtest answer ids
    function magtest_answer_ids($course) {
        global $CFG;

        $query = "
            SELECT 
                ma.id, 
                m.course
            FROM 
                {$CFG->prefix}magtest m,
                {$CFG->prefix}magtest_answer ma
            WHERE
                m.id = ma.magtestid AND 
                m.course = '{$course}'
        ";
        return get_records_sql($query);
    }

    // Returns an array of magtest user choice ids
    function magtest_useranswer_ids($course) {
        global $CFG;

        $query = "
            SELECT 
                mua.id, 
                m.course
            FROM 
                {$CFG->prefix}magtest m,
                {$CFG->prefix}magtest_useranswer mua
            WHERE
                m.id = mua.magtestid AND 
                m.course = '{$course}'
        ";
        return get_records_sql($query);
    }

?>