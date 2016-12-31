<?php
/**
 * User: jdenoc
 * Date: 3/5/14
 * Time: 7:55 PM
 */

require_once __DIR__.'/../includes/ProcessData.php';

$user = ProcessData::get_db_object()->get("users", 'id', array('email'=>$_REQUEST['email']));
if(empty($user)){
    print 0;
} else {
    $session_title = require __DIR__ . '/../config/config.session.php';
    session_name($session_title);
    session_start();
    $_SESSION['name'] = $_REQUEST['name'];
    $_SESSION['pic'] = $_REQUEST['pic'];
    $_SESSION['email'] = $_REQUEST['email'];

    print 1;
}