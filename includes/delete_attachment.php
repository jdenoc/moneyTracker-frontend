<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 3/23/14
 * Time: 10:31 PM
 */

require_once(__DIR__.'/../Lib/php/PDO_Connection.php');
$db = new PDO_Connection('jdenocco_receipt', __DIR__.'/../config/config.db.php');        // TODO - change DB name to money_tracker

$db->delete('attachments', 'id=:attachment_id', array('attachment_id'=>intval($_REQUEST['id'])));
$count = $db->getValue(
    "SELECT COUNT(id) FROM attachments WHERE entry_id=:entry_id",
    array('entry_id'=>intval($_REQUEST['entry_id']))
);
if($count < 1){
    $db->update(
        'entries',
        array('has_attachment'=>0),
        'id=:entry_id',
        array('entry_id'=>intval($_REQUEST['entry_id']))
    );
    echo 0;
} else {
    echo 1;
}