<?php

require_once(__DIR__ . '/ProcessData.php');
set_exception_handler('exception_handler');

$post = false;
$post_data = array();
switch($_REQUEST['type']){
    case 'count':
        $uri = '/count';
        $post = true;
        $post_data = array('where'=>base64_encode(ProcessData::clean_input('where')));
        $callback = 'do_nothing';
        break;

    case 'tags':
        $uri = '/tags';
        $callback = 'decode';
        break;

    case 'delete_attachment':
        $uri = '/delete/id/'.ProcessData::clean_input('entry_id').'/attachment/'.ProcessData::clean_input('id');
        $callback = 'do_nothing';
        break;

    case 'delete_entry':
        $uri = '/delete/id/'.ProcessData::clean_input('id');
        $callback = 'do_nothing';
        break;

    case 'list_accounts':
        $uri = '/list_accounts';
        $callback = 'list_accounts';
        break;

    case 'list':
        $uri = '/list';
        $post = true;
        $limit = empty($_POST['limit']) ? 50 : $_POST['limit'];
        $post_data = array(
            'start'=>intval(ProcessData::clean_input('start')),
            'limit'=>$limit,
            'where'=>base64_encode(ProcessData::clean_input('where'))
        );
        $callback = 'list_entries';
        break;

    case 'entry':
        $uri = '/entry/id/'.$_POST['id'];
        $callback = 'decode';
        break;

    case 'save':
        $uri = '/save';
        $post = true;
        $data = json_decode(ProcessData::clean_input('entry_data'), true);
        $data['entry_date'] = $data['date'];
        $data['entry_value'] = $data['value'];
        $data['account_type'] = $data['accountType'];
        $data['attachments'] = ProcessData::upload_attachments($data['attachments']);
        unset($data['date'], $data['value'], $data['accountType']);
        $post_data = array('data'=>base64_encode(json_encode($data)));
        $callback = 'do_nothing';
        unset($data);
        break;

    case 'get_account_data':
        $uri = '/account_details';
        $callback = 'display_account_settings';
        break;

    case 'save_account_type':
        $uri = '/save_account_type';
        $post = true;
        $data = json_encode(ProcessData::clean_input('type_data'));
        $post_data = array('data'=>base64_encode($data));
        $callback = 'do_nothing';
        unset($data);
        break;
    
    case 'disable_account_type':
        $uri = '/disable_account_type';
        $post = true;
        $data = json_encode(ProcessData::clean_input('type_data'));
        $post_data = array('data'=>base64_encode($data));
        $callback = 'do_nothing';
        unset($data);
        break;
    
    default:
        $uri = '';
        $callback = 'do_nothing';
}

$json_response = ProcessData::make_call(ProcessData::get_base_rest_url().$uri, $post, $post_data);

if(!$response_array = json_decode($json_response, true)){
    fatal_error_response("failed JSON response\n".$json_response);
} else {
    if(empty($response_array['error'])){
        $response = call_user_func(array('ProcessData', $callback), $response_array['result']);
        echo $response;
    } else {
        error_log(ProcessData::ERROR_LABEL."response error\n".$response_array['error']);
        header("HTTP/1.1 400 Bad Request");
        exit;
    }
}

/**
 * causes the script to die, with a 500 error and then log an error.
 * @param string $log_msg
 */
function fatal_error_response($log_msg){
    error_log(ProcessData::ERROR_LABEL.$log_msg);
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

/**
 * Handles any exceptions that may occur
 * @param Exception $e
 */
function exception_handler(Exception $e){
    fatal_error_response($e);
}