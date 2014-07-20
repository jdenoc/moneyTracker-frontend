<?php
/**
 * User: denis
 * Date: 3/11/14
 */

require_once(__DIR__.'/connection.php');
$db = new pdo_connection('jdenocco_receipt');        // TODO - change DB name to money_tracker

$where_array = empty($_REQUEST['where']) ? array() : json_decode($_REQUEST['where'], true);
$where_stmt = array('entries.deleted=0');

if(!empty($where_array['start_date']))
    $where_stmt[] = "entries.`date` >= :start_date";
if(!empty($where_array['end_date']))
    $where_stmt[] = "entries.`date` <= :end_date";
if(!empty($where_array['account_type']))
    $where_stmt[] = "entries.account_type = :account_type";
if(isset($where_array['attachments']) && in_array($where_array['attachments'], array(0,1)))
    $where_stmt[] = "entries.has_attachment = :attachments";
if(!empty($where_array['confirm'])){
    $where_stmt[] = "entries.confirm=0";
    unset($where_array['confirm']);
}
if(!empty($where_array['min_value']))
    $where_stmt[] = "entries.value >= :min_value";
if(!empty($where_array['max_value']))
    $where_stmt[] = "entries.value <= :max_value";
if(!empty($where_array['group']))
    $where_stmt[] = "account_types.account_group = :group";
if(!empty($where_array['tags'])){
    foreach($where_array['tags'] as $tag){
        $tag_array = array();
        $tag_array[] = "entries.tags LIKE '[".$tag."]'";
        $tag_array[] = "entries.tags LIKE '[".$tag.",%'";
        $tag_array[] = "entries.tags LIKE '%,".$tag.",%'";
        $tag_array[] = "entries.tags LIKE '%,".$tag."]'";
        $where_stmt[] = '('.implode(" OR ", $tag_array).')';
    }
}
unset($where_array['tags']);

$count = $db->count(
    "entries INNER JOIN account_types ON account_types.id = entries.account_type",
    implode(' AND ', $where_stmt),
    $where_array
);
print $count;