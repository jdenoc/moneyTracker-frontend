<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 3/8/14
 * Time: 3:23 PM
 */

require_once(__DIR__.'/connection.php');
$db = new pdo_connection('jdenocco_receipt');        // TODO - change DB name to money_tracker
$tags = $db->getAllRows("SELECT * FROM tags");
$lmt = $_REQUEST['limit']*50;
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
if(isset($where_array['expense']) && in_array($where_array['expense'], array(0,1)))
    $where_stmt[] = "entries.expense = :expense";
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

$where = ' WHERE '.implode(' AND ', $where_stmt).' ';
$entries = $db->getAllRows(
    "SELECT
        entries.*,
        account_types.type_name AS account_type_name,
        account_types.last_digits AS account_last_digits
    FROM entries
    INNER JOIN account_types ON account_types.id = entries.account_type
    ".$where."
    ORDER BY entries.`date` DESC, entries.id DESC
    LIMIT ".$lmt.", 50",
    $where_array
);

// Display 1-50 entries
foreach($entries as $row){
    $tag_displays = '';
    if(!empty($row['tags'])){
        $tag_ids = json_decode($row['tags'], true);
        foreach($tags as $t){
            if(in_array($t['id'], $tag_ids)){
                $tag_displays .= '<span class="label label-default">'.$t['tag'].'</span><br/>'."\r\n";
            }
        }
    }
    echo '<tr class="'.(!$row['confirm'] ? 'warning' : (!$row['expense'] ? 'success' : '' )).'">';
    echo '  <td class="check-col" data-toggle="modal" data-target="#entry-modal" onclick="editDisplay.fill('.$row['id'].');">';
    echo '      <span class="glyphicon glyphicon-pencil"></span>';
    echo '  </td>';
    echo '  <td class="date-col">'.$row['date'].'</td>';
    echo '  <td>'.$row['memo'].'</td>';
    echo '  <td class="value-col">$'.number_format($row['value'], 2).'</td>';
    echo '  <td class="type-col"><span class="glyphicon glyphicon-list-alt" onclick="alert(\''.$row['account_type_name'].' ('.$row['account_last_digits'].')\n'.($row['expense']?'Expense':'Income').($row['confirm']?'\nConfirmed':'').'\')"></span></td>';
    echo '  <td><input type="checkbox" '.($row['has_attachment']==1 ? 'checked' : '' ).' onclick="return false;" /></td>';
    echo '  <td>'.$tag_displays.'</td>';
    echo '</tr>';
}
