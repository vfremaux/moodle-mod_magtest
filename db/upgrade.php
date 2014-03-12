<?php

function xmldb_magtest_upgrade($oldversion=0) {
/// This function does anything necessary to upgrade 
/// older versions to match current functionality 

    global $CFG, $DB;
    
    $result = true;
    
    $dbman = $DB->get_manager();

    if ($oldversion < 2008053100) {

    /// Define field course to be added to magtest
        $table = new xmldb_table('magtest');
        $field = new xmldb_field('course');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'id');

    /// Launch add field course
        if (!$dbman->field_exists($table, $field)){
        	$dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2008053100, 'magtest');
    }

    if ($oldversion < 2009053100) {

        $table = new xmldb_table('magtest_answer');
        $field = new xmldb_field('helper');
		$field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null,  null, 'format');
        if (!$dbman->field_exists($table, $field)){
	        $dbman->add_field($table, $field);
	    }

        $field = new xmldb_field('helperformat');
		$field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'helper');
        if (!$dbman->field_exists($table, $field)){
	        $dbman->add_field($table, $field);
	    }

        $field = new xmldb_field('weight');
		$field->set_attributes(XMLDB_TYPE_NUMBER, '10', null, XMLDB_NOTNULL, null, 1, 'categoryid');
        if (!$dbman->field_exists($table, $field)){
	        $dbman->add_field($table, $field);
	    }
        upgrade_mod_savepoint(true, 2009053100, 'magtest');
    }
    
    // Moodle 2.0 upgrade horizon

    if ($oldversion < 2012103100) {

    /// Define field course to be added to magtest
        $table = new xmldb_table('magtest');
        $field = new xmldb_field('description');
		$field->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null,  null, 'id');
        if ($dbman->field_exists($table, $field)){
	        $dbman->rename_field($table, $field, 'intro', false);
	    }

        $field = new xmldb_field('introformat');
		$field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null,  0, 'intro');
        if (!$dbman->field_exists($table, $field)){
	        $dbman->add_field($table, $field);
	    }

        $table = new xmldb_table('magtest_answer');
        $field = new xmldb_field('format');
		$field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null,  0, 'answertext');
        if ($dbman->field_exists($table, $field)){
        	
        	// pre-fix possible old NULL values 
        	$sql = "
        		UPDATE
        			{magtest_answer}
        		SET
        			format = 0
        		WHERE
        			format IS NULL
        	";
        	$DB->execute($sql);
        	
	        $dbman->rename_field($table, $field, 'answertextformat', false);
	    }

        $table = new xmldb_table('magtest_question');
        $field = new xmldb_field('format');
		$field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null,  0, 'questiontext');
        if ($dbman->field_exists($table, $field)){

        	// pre-fix possible old NULL values 
        	$sql = "
        		UPDATE
        			{magtest_question}
        		SET
        			format = 0
        		WHERE
        			format IS NULL
        	";
        	$DB->execute($sql);

	        $dbman->rename_field($table, $field, 'questiontextformat', false);
	    }

        $table = new xmldb_table('magtest_category');

        $field = new xmldb_field('format');
		$field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null,  0, 'name');
        if ($dbman->field_exists($table, $field)){

        	// pre-fix possible old NULL values 
        	$sql = "
        		UPDATE
        			{magtest_category}
        		SET
        			format = 0
        		WHERE
        			format IS NULL
        	";
        	$DB->execute($sql);

	        $dbman->rename_field($table, $field, 'descriptionformat', false);
	    }

        $field = new xmldb_field('outputgroupname');
		$field->set_attributes(XMLDB_TYPE_CHAR, '32', null, null, null,  null, 'result');
        if (!$dbman->field_exists($table, $field)){
	        $dbman->add_field($table, $field);
	    }

        $field = new xmldb_field('outputgroupdesc');
		$field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null,  null, 'outputgroupname');
        if (!$dbman->field_exists($table, $field)){
	        $dbman->add_field($table, $field);
	    }

        upgrade_mod_savepoint(true, 2012103100, 'magtest');
    }

    if ($oldversion < 2014012802) {

        $table = new xmldb_table('magtest');
        $field = new xmldb_field('singlechoice');
		$field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null,  0, 'allowreplay');
        if (!$dbman->field_exists($table, $field)){
	        $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2014012802, 'magtest');
    }
    
    return $result;
}
