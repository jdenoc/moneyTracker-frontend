<?php
/**
 * User: jdenoc
 * Date: 3/5/14
 * Time: 7:55 PM
 */

require_once(__DIR__.'/../Lib/php/PDO_Connection.php');
$db = new PDO_Connection('jdenocco_receipt', __DIR__.'/../config/config.db.php');        // TODO - change DB name to money_tracker
$user = $db->getRow("SELECT id FROM users WHERE email=:email", array('email'=>$_REQUEST['email']));
if(empty($user)){
    print 0;
} else {
    $session_title = include_once(__DIR__ . '/../config/config.session.php');
    session_name($session_title);
    session_start();
    $_SESSION['name'] = $_REQUEST['name'];
    $_SESSION['pic'] = $_REQUEST['pic'];
    $_SESSION['email'] = $_REQUEST['email'];

    print 1;
}