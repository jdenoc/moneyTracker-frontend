<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 3/23/14
 * Time: 10:31 PM
 */

require_once(__DIR__.'/connection.php');
$db = new pdo_connection('jdenocco_receipt');        // TODO - change DB name to money_tracker

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