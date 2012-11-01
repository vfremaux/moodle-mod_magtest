<?php

function xmldb_magtest_upgrade($oldversion=0) {
/// This function does anything necessary to upgrade 
/// older versions to match current functionality 

    global $CFG;
    
    $result = true;

    if ($result && $oldversion < 2008053100) {

    /// Define field course to be added to magtest
        $table = new XMLDBTable('magtest');
        $field = new XMLDBField('course');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0, 'id');

    /// Launch add field course
        $result = $result && add_field($table, $field);
    }
    
    return $result;
}
?>