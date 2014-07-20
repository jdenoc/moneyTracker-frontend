<?php

$session_title = include_once(__DIR__.'/../includes/config.session.php');
session_name($session_title);
session_start();

if(empty($_SESSION['email'])){
    header('HTTP/1.0 404 Not Found');
    exit;
}

require_once(__DIR__.'/../includes/connection.php');

$id = intval($_REQUEST['id']);
$db = new pdo_connection('jdenocco_receipt');        // TODO - change DB name to money_tracker

$attachment = $db->getRow("SELECT * FROM attachments WHERE id=:attachment_id;", array('attachment_id'=>$id));
$md5 = include_once(__DIR__.'/../includes/config.md5.php');
$filename ='../receipts_attachments/'. md5($attachment['attachment'].$md5).$attachment['ext'];
$size = getimagesize($filename);
while($size[0]>700){
    $size[0] /= 2;
    $size[1] /= 2;
}

header('Content-Type: text/css');
echo "img{\r\n";
echo "  background-image: url(".$filename.");\r\n";
echo "  background-size: ".$size[0]."px ".$size[1]."px;\r\n";    // 0: width; 1: height;
echo "  width: ".$size[0]."px;\r\n";
echo "  height: ".$size[1]."px;\r\n";
echo "}\r\n";