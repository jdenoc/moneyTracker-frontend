<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 3/7/14
 * Time: 12:42 AM
 */

require_once(__DIR__.'/connection.php');
$db = new pdo_connection('jdenocco_receipt');        // TODO - change DB name to money_tracker

$valid_tags = $_REQUEST['valid_tags'];
$data = json_decode($_REQUEST['entry_data'], true);

// Attachment uploader
$md5 = include_once(__DIR__.'/config.md5.php');
$output_dir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."receipts_attachments".DIRECTORY_SEPARATOR;
$tmp_dir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR;
$has_attachment = $data['has_attachment'];
if(!empty($data['attachments'])){
    foreach($data['attachments'] as $attachment){
        $has_attachment = 1;
        $pos = strrpos($attachment, '.');
        $ext = substr($attachment, $pos);
        rename($tmp_dir.$attachment,$output_dir.md5($attachment.$md5).$ext);
        $db->insert('attachments', array(
            'entry_id'=>$data['id'],
            'attachment'=>$attachment,
            'ext'=>$ext
        ));
    }
}
// END - Attachment uploader

$tags = implode("','", $data['tags']);
$tag_ids = $db->getAllValues("SELECT id FROM tags WHERE tag IN ('$tags')");
$tag_ids = (empty($tag_ids)) ? '' : json_encode($tag_ids);

$entry =  array(
    'date'=>$data['date'],
    'value'=>$data['value'],
    'account_type'=>$data['account_type'],
    'memo'=>$data['memo'],
    'tags'=>$tag_ids,
    'has_attachment'=>$has_attachment,
    'confirm'=>$data['confirm'],
    'expense'=>$data['expense']
);

$data['value'] *= ($data['expense']) ? -1 : 1;
if($data['id'] == -1){
    $account = $db->getRow(
        "SELECT a.* FROM accounts AS a
        INNER JOIN account_types AS `at` ON a.id=`at`.account_group
        WHERE `at`.id=:account_type",
        array('account_type'=>$data['account_type'])
    );
    $db->insert('entries', $entry);
    $entry_id = $db->getValue("SELECT LAST_INSERT_ID()");
    $db->update(
        'attachments',
        array('entry_id'=>$entry_id,),
        'entry_id=-1'
    );

} else {
    $account = $db->getRow(
        "SELECT
            a.id AS id,
            a.total AS total,
            e.value AS `value`,
            e.expense AS expense
        FROM accounts AS a
        INNER JOIN account_types AS `at` ON a.id=`at`.account_group
        INNER JOIN entries AS e ON e.account_type=`at`.id
        WHERE e.id=:entry_id",
        array('entry_id'=>$data['id'])
    );
    $account['total'] -= (($account['expense']?-1:1)*$account['value']);
    $db->update(
        'entries',
        $entry,
        'id=:entry_id',
        array('entry_id'=>$data['id'])
    );
}

$db->update('accounts',
    array('total'=>($account['total']+$data['value'])),
    'id=:account_id',
    array('account_id'=>$account['id'])
);