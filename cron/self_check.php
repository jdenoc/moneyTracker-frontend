<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 4/10/14
 * Time: 9:21 PM
 *
 * TODO - calculate the correct totals.
 * TODO - If there is a discrepancy, then email.
 */

require_once __DIR__.'/../vendor/autoload.php';

$db_config = require __DIR__.'/../config/config.db.php';
$db = new medoo(array(
    'database_type' => 'mysql',
    'database_name' => $db_config['database'],
    'server' => $db_config['hostname'],
    'username' => $db_config['username'],
    'password' => $db_config['password'],
    'charset' => 'utf8mb64'
));

$accounts = $db->select('accounts', '*');
$msg = "";
foreach($accounts AS $account){
    $should_be = $db->getValue(
        "SELECT SUM( IF( e.expense=1, -1*e.value, e.value ) )
            FROM entries AS e
            INNER JOIN account_types AS a ON a.id = e.account_type
            WHERE a.account_group = :account_group
            AND e.deleted =0
            ORDER BY e.`date` DESC , e.id DESC",
        array('account_group'=>$account['id'])
    );

    if($should_be != $account['total']){
        $msg .= 'Account:'.$account['account']."\r\n\tIS: $".$account['total']."\r\n\tShould Be: $".$should_be."\r\n\tDiff: $".abs($should_be-$account['total'])."\r\n";
    }
}

if(!empty($msg)){
    // tell someone this sucks
    mail('info@jdenoc.com', 'Account total issues', 'Date: '.date('Y-m-d')."\r\n".$msg);
}