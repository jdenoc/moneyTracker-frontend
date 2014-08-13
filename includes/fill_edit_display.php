<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 2014-03-03
 */

require_once(__DIR__.'/../Lib/php/PDO_Connection.php');

$id = $_REQUEST['id'];
$db = new PDO_Connection('jdenocco_receipt', __DIR__.'/../config/config.db.php');        // TODO - change DB name to money_tracker

$entry = $db->getRow(
    "SELECT
        entries.*,
        account_types.type_name AS account_type_name,
        account_types.last_digits AS account_last_digits
    FROM entries
    INNER JOIN account_types ON account_types.id = entries.account_type
    WHERE entries.id=:entry_id
    ORDER BY entries.`date` DESC",
    array('entry_id'=>$id)
);

if($entry['has_attachment']==1){
    $attachments = $db->getAllRows("SELECT id, attachment AS filename FROM attachments WHERE entry_id=:entry_id", array('entry_id'=>$id));
    if(empty($attachments)){
        $entry['has_attachment']=0;
        $entry['attachments'] = array();
    } else {
        $entry['attachments'] = $attachments;
    }
}

$tag_ids = json_decode($entry['tags'], true);
if(!empty($tag_ids)){
    $entry['tags'] = $db->getAllRows("SELECT * FROM tags WHERE id IN (".implode(',', $tag_ids).")");
}

print json_encode($entry);
