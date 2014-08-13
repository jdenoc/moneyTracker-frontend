<?php
/**
 * User: denis
 * Date: 2014-08-09
 */

require_once(__DIR__.'/process_data.php');

$post = false;
$post_data = array();
switch($_REQUEST['type']){
    case 'count':
        $uri = 'count';
        $post = true;
        $post_data = array('where'=>ProcessData::clean_input('where'));
        $callback = 'do_nothing';
        break;

    case 'tags':
        $uri = 'tags';
        $callback = 'base_process';
        break;

    case 'delete_attachment':
        $uri = 'delete/id/'.ProcessData::clean_input('entry_id').'/attachment/'.ProcessData::clean_input('id');
        $callback = 'do_nothing';
        break;

    case 'delete_entry':
        $uri = 'delete/id/'.ProcessData::clean_input('id');
        $callback = 'do_nothing';
        break;

    case 'list_accounts':
        $uri = 'list_accounts';
        $callback = 'list_accounts';
        break;

    case 'list':
        $uri = 'list';
        $post = true;
        $limit = empty($_POST['limit']) ? 50 : $_POST['limit'];
        $post_data = array('start'=>intval(ProcessData::clean_input('start')), 'limit'=>$limit, 'where'=>ProcessData::clean_input('where'));
        $callback = 'list_entries';
        break;

    case 'get':
        $uri = 'entry/id/'.$_POST['id'];
        $callback = 'base_process';
        break;

    case 'save':
        $uri = 'save';
        $post = true;
        $post_data = array('data'=>ProcessData::clean_input('entry_data'));
        $callback = 'do_nothing';
        break;

    default:
        $uri = '';
        $callback = 'do_nothing';
}

$api_url = 'http://services.local/index.php/api/money_tracker/';
$json_response = ProcessData::make_call($api_url.$uri, $post, $post_data);

if(!$response_array = json_decode($json_response, true)){
    error_log(ProcessData::$error_title.$json_response);
} else {
    if(empty($response_array['error'])){
        $response = call_user_func(array('ProcessData', $callback), $response_array['result']);
        echo $response;
    } else {
        error_log(ProcessData::$error_title.$response_array['error']);
    }
}