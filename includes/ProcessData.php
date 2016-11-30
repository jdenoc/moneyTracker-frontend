<?php
/**
 * User: denis
 * Date: 2014-08-12
 */

require_once __DIR__.'/../vendor/autoload.php';
use Ramsey\Uuid\Uuid;

class ProcessData {

    private static $auth;
    public static $error_title = 'Money-Tracker Request Error:';

    public static function make_call($url, $post=false, $post_data=array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:'.self::get_auth()
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_POST, $post);
        if($post){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log(self::$error_title."services connection issue\nURL:".$url."\n".curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    public static function clean_input($post){
        return empty($_POST[$post]) ? '' : $_POST[$post];
    }

    /**
     * @param string $filename
     * @param string $file_uid
     * @return string
     */
    public static function hash_filename($filename, $file_uid){
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return md5($filename.$file_uid).'.'.$ext;
    }

    /**
     * @return string
     */
    public static function generate_file_uid(){
        $uuid4 = Uuid::uuid4();
        return $uuid4->toString();
    }

    public static function list_accounts($data){
        $accounts = self::base_process($data);
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

        $json_response = self::make_call(self::get_url().'tags');
        if(!$tags_data = json_decode($json_response, true)){
            error_log(self::$error_title.$json_response);
            $tags = array();
        } else {
            if(empty($response_array['error'])){
                $tags = self::base_process($tags_data['result']);
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

    private static function base_process($data){
        return json_decode(self::decode($data), true);
    }

    public static function do_nothing($data){
        return $data;
    }

    private static function get_auth(){
        if(is_null(self::$auth)){
            self::set_auth();
        }
        return self::$auth;
    }
    
    private static function set_auth(){
        // TODO - find a better way to obtain this value. Maybe from a DB.
        self::$auth = 'test';
    }

    public static function decode($data){
        return base64_decode($data);
    }
    
    private static function get_env(){
        return getenv('ENV_TYPE');
    }
    
    public static function get_url(){
        if(self::get_env() == 'live'){
            return 'https://services.jdenoc.com/api/money_tracker/';
        } else {
            return 'http://services.local/api/money_tracker/';
        }
    }

    /**
     * @param string $attachment_name
     * @param string $attachment_uid
     * @return bool
     */
    public static function upload_attachment($attachment_name, $attachment_uid){
        $output_dir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."receipts_attachments".DIRECTORY_SEPARATOR;
        $temp_file = sys_get_temp_dir().DIRECTORY_SEPARATOR.$attachment_name;
        return (file_exists($temp_file) && rename($temp_file, $output_dir.self::hash_filename($attachment_name, $attachment_uid)));
    }

    /**
     * @param array $attachments
     * @return array
     */
    public static function upload_attachments($attachments){
        $uploaded_attachments = array();
        foreach($attachments as $attachment){
            $uid = self::generate_file_uid();
            $uploaded = self::upload_attachment($attachment, $uid);
            if($uploaded){
                $uploaded_attachments[] = array('filename'=>$attachment, 'uid'=>$uid);
            }
        }
        return $uploaded_attachments;
    }

    public static function display_account_settings($data){
        $account_data = self::base_process($data);
        $type_options = $account_data['types'];
        unset($account_data['types']);

        $display = '';
        $types = array();
        foreach($account_data as $id=>$account){
            $display .= '<tr id="account_setting_'.$id.'" class="account_setting"><td><h3>'.$account['account_name']."</h3><ul>\r\n";
            foreach($account['type'] as $type){
                $types[$id][$type['type_id']] = $type['type'];
                $display .= '<li id="type_'.$type['type_id'].'" class="account_type">';
                $display .= '<label>Name:<input type="text" name="type_name" class="form-control" value="'.$type['type_name'].'" readonly/></label>';
                $display .= '<label>Last Digits:<input type="text" name="last_digits" class="form-control" value="'.$type['last_digits'].'"  maxlength="4" readonly/></label>';
                $display .= '<label>Type: <select name="type" class="form-control" disabled></select></label>';
                $display .= '<button type="button" class="btn btn-default type_button edit_type">Edit</button>';
                $display .= '<button type="button" class="btn btn-default type_button save_type">Save</button>';
                $display .= '<button type="button" class="btn btn-default type_button cancel_type">Cancel</button>';
                $display .= '<button type="button" class="btn btn-default type_button disable_type">Disable</button>';
                $display .= "</div></li>\r\n";
            }
            $display .= "<li class='account_type add_type btn'>Add Account Type</li>\r\n";
            $display .= "</ul></td></tr>\r\n";
        }
        $display .= "<script type='text/javascript'>\r\n";
        $display .= "\tvar typeOptions = ".json_encode($type_options).";\r\n";
        $display .= "\tvar types = ".json_encode($types).";\r\n";
        $display .= "</script>";
        return $display;
    }
}