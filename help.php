<?php

include '../../config.php';

require_login();

$answerid = required_param('answerid', PARAM_INT);
$answer = $DB->get_record('magtest_answer', array('id' => $answerid));
echo $answer->helper;

?>