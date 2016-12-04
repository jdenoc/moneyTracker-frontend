<?php

$session_title = include_once(__DIR__ . '/../config/config.session.php');
session_name($session_title);
session_start();

if(empty($_SESSION['email'])){
    header('HTTP/1.0 404 Not Found');
    exit;
}

require_once __DIR__.'/../includes/ProcessData.php';

$attachment_id = intval($_REQUEST['id']);
$attachment = ProcessData::get_db_object()->get('attachments', array('attachment', 'uid'), array('id'=>$attachment_id));
// file must be relative. Browser can't display absolute paths
$filename = 'receipts_attachments/'.ProcessData::hash_filename($attachment['attachment'], $attachment['uid']);

$size = getimagesize(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$filename);
while($size[0]>700){
    $size[0] /= 2;
    $size[1] /= 2;
}

header('Content-Type: text/css');
echo "img{\r\n";
echo "  background-image: url(/".$filename.");\r\n";
echo "  background-size: ".$size[0]."px ".$size[1]."px;\r\n";    // 0: width; 1: height;
echo "  width: ".$size[0]."px;\r\n";
echo "  height: ".$size[1]."px;\r\n";
echo "}\r\n";