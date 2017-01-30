<?php

require_once __DIR__.'/includes/ProcessData.php';

session_name(ProcessData::get_env_value("SESSION_NAME"));
session_start();

if(empty($_SESSION['email'])){
    display_404();
}

$attachment_id = empty($_REQUEST['uuid']) ? '' : $_REQUEST['uuid'];
if(!ProcessData::is_valid_uuid($attachment_id)){
    display_404();
}

$attachment = ProcessData::get_db_object()->get('attachments', 'attachment', array('uuid'=>$attachment_id));
$filename = __DIR__.DIRECTORY_SEPARATOR.'receipts_attachments'.DIRECTORY_SEPARATOR.ProcessData::hash_filename($attachment, $attachment_id);

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
        display_image($attachment_id);
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