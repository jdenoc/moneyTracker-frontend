<?php

require_once __DIR__.'/../includes/ProcessData.php';

$user = ProcessData::get_db_object()->get("users", 'id', array('email'=>$_REQUEST['email']));
if(empty($user)){
    print 0;
} else {
    session_name(getenv("SESSION_NAME"));
    session_start();
    $_SESSION['name'] = $_REQUEST['name'];
    $_SESSION['pic'] = $_REQUEST['pic'];
    $_SESSION['email'] = $_REQUEST['email'];

    print 1;
}