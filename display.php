<?php
/**
 * User: denis
 * Date: 3/23/14
 */
$session_title = include_once(__DIR__ . '/config/config.session.php');
session_name($session_title);
session_start();

if(empty($_SESSION['email'])){
    display404();
}

require_once(__DIR__.'/Lib/php/PDO_Connection.php');

$id = intval($_REQUEST['id']);
$db_config = require(__DIR__.'/config/config.db.php');
$db = new PDO_Connection($db_config['database'], $db_config['username'], $db_config['password'], $db_config['hostname']);
unset($db_config);

$attachment = $db->getRow("SELECT * FROM attachments WHERE id=:attachment_id;", array('attachment_id'=>$id));
$md5 = include_once(__DIR__ . '/config/config.md5.php');
$filename ='receipts_attachments/'. md5($attachment['attachment'].$md5).$attachment['ext'];

if(!file_exists($filename)){
    display404();
}

switch(substr(strtolower($attachment['ext']), 1)){
    case 'pdf':
        displayPDF($filename);
        break;
    case 'png':
    case 'gif':
    case 'jpg':
    case 'jpeg':
        displayImage($id);
        break;
    default:
        // Do nothing.
}

function displayPDF($file){
    header('Content-type: application/pdf');
    readfile($file);
}
function displayImage($id){
    echo "<html>\r\n";
    echo "  <head>\r\n";
    echo "      <title>Attachment</title>\r\n";
    echo "      <link href='css/attachment.php?id=".$id."' type='text/css' rel='stylesheet' />\r\n";
    echo "  </head>\r\n";
    echo "  <body oncontextmenu='return false;'><img src='imgs/foreground.png' alt='attachment'/></body>\r\n";
    echo "</html>\r\n";
}
function display404(){
    header('HTTP/1.0 404 Not Found');
    exit;
}