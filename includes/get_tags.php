<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 3/5/14
 * Time: 11:57 PM
 */

require_once(__DIR__.'/connection.php');
$db = new pdo_connection('jdenocco_receipt');        // TODO - change DB name to money_tracker

$tags = $db->getAllRows("SELECT * FROM tags");
header('Content-Type: application/json');
print json_encode($tags);
