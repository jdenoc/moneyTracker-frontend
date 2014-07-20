<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 3/9/14
 * Time: 5:03 PM
 */

require_once(__DIR__.'/connection.php');
$db = new pdo_connection('jdenocco_receipt');        // TODO - change DB name to money_tracker
$accounts = $db->getAllRows("SELECT * FROM accounts");
$account_position = 3;
foreach($accounts as $account){
    echo '<li><a href="#" onclick="resetFilter();displayAccount({\'group\':'.$account['id'].'}, '.$account_position.')">'.$account['account'].'<br/>$'.number_format($account['total'], 2).'</a></li>';
    $account_position++;
}