<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 3/5/14
 * Time: 11:57 PM
 */

require_once(__DIR__.'/../Lib/php/PDO_Connection.php');
$db = new PDO_Connection('jdenocco_receipt', __DIR__.'/../config/config.db.php');        // TODO - change DB name to money_tracker

$tags = $db->getAllRows("SELECT * FROM tags");
header('Content-Type: application/json');
print json_encode($tags);
