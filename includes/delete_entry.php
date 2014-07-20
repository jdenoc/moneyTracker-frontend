<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 3/8/14
 * Time: 2:37 PM
 */

require_once(__DIR__.'/connection.php');
$db = new pdo_connection('jdenocco_receipt');        // TODO - change DB name to money_tracker

$data = $db->getRow(
    "SELECT
        a.id AS id,
        a.total AS total,
        e.value AS `value`,
        e.expense AS expense
    FROM accounts AS a
    INNER JOIN account_types AS `at` ON a.id=`at`.account_group
    INNER JOIN entries AS e ON e.account_type=`at`.id
    WHERE e.id=:entry_id",
    array('entry_id'=>$_REQUEST['id'])
);
$db->update('entries',
    array('deleted'=>1),
    'id=:entry_id',
    array('entry_id'=>$_REQUEST['id'])
);
$data['value'] *= $data['expense'] ? -1 : 1;
$db->update('accounts',
    array('total'=>($data['total']-$data['value'])),
    'id=:account_id',
    array('account_id'=>$data['id'])
);
// TODO - delete all attachments. In DB and physical.