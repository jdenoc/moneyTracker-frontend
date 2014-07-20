<?php
/**
 * User: jdenoc
 * Date: 3/5/14
 * Time: 7:55 PM
 */

require_once(__DIR__.'/connection.php');
$db = new pdo_connection('jdenocco_receipt');        // TODO - change DB name to money_tracker
$user = $db->getRow("SELECT id FROM users WHERE email=:email", array('email'=>$_REQUEST['email']));
if(empty($user)){
    print 0;
} else {
    $session_title = include_once(__DIR__.'/config.session.php');
    session_name($session_title);
    session_start();
    $_SESSION['name'] = $_REQUEST['name'];
    $_SESSION['pic'] = $_REQUEST['pic'];
    $_SESSION['email'] = $_REQUEST['email'];

    print 1;
}