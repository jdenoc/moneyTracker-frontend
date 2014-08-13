<?php
/**
 * User: denis
 * Date: 2014-08-12
 */

class ProcessData {

    private static $auth = 'test';
    public static $error_title = 'Money-Tracker Request Error:';

    public static function make_call($url, $post=false, $post_data=array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:'.self::get_auth()
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_POST, $post);
        if($post){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log(self::$error_title." services connection issue-".curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    public static function clean_input($post){
        return empty($_POST[$post]) ? '' : $_POST[$post];
    }

    public static function list_accounts($data){
        // TODO - test
        $accounts = json_decode(base64_decode($data), true);
        $display = '';
        $account_position = 3;
        foreach($accounts as $account){
            $display .= '<li><a href="#" onclick="filter.reset();displayAccount({\'group\':'.$account['id'].'}, '.$account_position.')">'.$account['account'].'<br/>$'.number_format($account['total'], 2).'</a></li>'."\r\n";
            $account_position++;
        }
        return $display;
    }

    public static function list_entries($data){
        $entries = self::base_process($data);
        $display = '';

        $json_response = self::make_call('tags');
        if(!$tags_data = json_decode($json_response, true)){
            error_log(self::$error_title.$json_response);
            $tags = array();
        } else {
            if(empty($response_array['error'])){
                $tags = self::base_process($tags_data['']);
            } else {
                error_log(self::$error_title.$response_array['error']);
                $tags = array();
            }
        }

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
            $display .= '<tr class="'.(!$row['confirm'] ? 'warning' : (!$row['expense'] ? 'success' : '' )).'">';
            $display .= '  <td class="check-col" data-toggle="modal" data-target="#entry-modal" onclick="editDisplay.fill('.$row['id'].');">';
            $display .= '      <span class="glyphicon glyphicon-pencil"></span>';
            $display .= '  </td>';
            $display .= '  <td class="date-col">'.$row['date'].'</td>';
            $display .= '  <td>'.$row['memo'].'</td>';
            $display .= '  <td class="value-col">$'.number_format($row['value'], 2).'</td>';
            $display .= '  <td class="type-col"><span class="glyphicon glyphicon-list-alt" onclick="alert(\''.$row['account_type_name'].' ('.$row['account_last_digits'].')\n'.($row['expense']?'Expense':'Income').($row['confirm']?'\nConfirmed':'').'\')"></span></td>';
            $display .= '  <td><input type="checkbox" '.($row['has_attachment']==1 ? 'checked' : '' ).' onclick="return false;" /></td>';
            $display .= '  <td>'.$tag_displays.'</td>';
            $display .= '</tr>';
        }
        return $display;
    }

    public static function base_process($data){
        return json_decode(base64_decode($data), true);
    }

    public static function do_nothing($data){
        return $data;
    }

    public static function get_auth(){
        return self::$auth;
    }

    public static function display($secret){
        // TODO - rebuild?
    }
}