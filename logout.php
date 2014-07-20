<?php
/**
 * User: denis
 * Date: 3/26/14
 * Time: 10:30 PM
 */

$session_title = include_once(__DIR__.'/includes/config.session.php');
session_name($session_title);
session_start();
$_SESSION['name'] = '';
$_SESSION['pic'] = '';
$_SESSION['email'] = '';

header("Location: index.php");
exit;