<?php

$session_title = include_once __DIR__ .'/config/config.session.php';
session_name($session_title);
session_start();

if(empty($_SESSION['email'])){
    display_404();
}

require_once __DIR__.'/Lib/php/PDO_Connection.php';
require_once __DIR__.'/includes/ProcessData.php';

$id = intval($_REQUEST['id']);
$db = new PDO_Connection('jdenoc_money_tracker', __DIR__.'/config/config.db.php');

$attachment = $db->getRow("SELECT * FROM attachments WHERE id=:attachment_id;", array('attachment_id'=>$id));
// file must be relative. Browser can't display absolute paths
$filename = __DIR__.DIRECTORY_SEPARATOR.'receipts_attachments'.DIRECTORY_SEPARATOR.ProcessData::hash_filename($attachment['attachment'], $attachment['uid']);

if(!file_exists($filename)){
    display_404();
}

$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
switch($ext){
    case 'pdf':
        display_pdf($filename);
        break;
    case 'png':
    case 'gif':
    case 'jpg':
    case 'jpeg':
        display_image($id);
        break;
    default:
        // Do nothing.
}

function display_pdf($file){
    header('Content-type: application/pdf');
    readfile($file);
}

function display_image($id){
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "      <title>Attachment</title>\r\n";
    echo "      <link href='/css/attachment/".$id."' type='text/css' rel='stylesheet' />\r\n";
    echo "  </head>\r\n";
    echo "  <body oncontextmenu='return false;'><img src='/imgs/foreground.png' alt='attachment'/></body>\r\n";
    echo "</html>\r\n";
}

function display_404(){
    header('HTTP/1.0 404 Not Found');
    header("Content-type: image/png");
    readfile(__DIR__.DIRECTORY_SEPARATOR.'imgs/attachment-not-found.png');
    exit;
}