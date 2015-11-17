<?php
/**
 * User: denis
 * Date: 2014-08-12
 */

class ProcessData {

    const ERROR_TITLE = 'Money-Tracker Request Error:';
    const CURL_TIMEOUT = 12; // CURL calls to timeout after 12 seconds

    private static $auth;

    public static function make_call($url, $post=false, $post_data=array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:'.self::get_auth()
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_POST, $post);
        if($post){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log(self::ERROR_TITLE."services connection issue\nURL:".$url."\n".curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    public static function clean_input($post){
        return empty($_POST[$post]) ? '' : $_POST[$post];
    }

    public static function list_accounts($accounts_data){
        $display = '';
        $account_position = 3;
        foreach($accounts_data as $account){
            $display .= '<li><a href="#" onclick="filter.reset();displayAccount({\'group\':'.$account['id'].'}, '.$account_position.')">'.$account['account'].'<br/>$'.number_format($account['total'], 2).'</a></li>'."\r\n";
            $account_position++;
        }
        return $display;
    }

    public static function list_entries($entries_data){
        $display = '';

        $json_response = self::make_call(self::get_url().'/tags');
        if(!$tags_data = json_decode($json_response, true)){
            error_log(self::ERROR_TITLE.$json_response);
            $tags = array();
        } else {
            if(empty($tags_data['error'])){
                $tags = $tags_data['result'];
            } else {
                error_log(self::ERROR_TITLE.$tags_data['error']);
                $tags = array();
            }
        }

        foreach($entries_data as $row){
            $tag_displays = '';
            if(!empty($row['tags'])){
                foreach($tags as $t){
                    if(in_array($t['id'], $row['tags'])){
                        $tag_displays .= '<span class="label label-default">'.$t['tag'].'</span><br/>'."\r\n";
                    }
                }
            }
            $display .= '<tr class="'.(!$row['confirm'] ? 'warning' : (!$row['expense'] ? 'success' : '' )).'">'."\r\n";
            $display .= '  <td class="check-col" data-toggle="modal" data-target="#entry-modal" onclick="editDisplay.fill('.$row['id'].');">'."\r\n";
            $display .= "      <span class=\"glyphicon glyphicon-pencil\"></span>\r\n";
            $display .= "  </td>\r\n";
            $display .= '  <td class="date-col">'.$row['entry_date']."</td>\r\n";
            $display .= '  <td>'.$row['memo']."</td>\r\n";
            $display .= '  <td class="value-col">$'.number_format($row['entry_value'], 2)."</td>\r\n";
            $display .= '  <td class="type-col"><span class="glyphicon glyphicon-list-alt" onclick="alert(\'';
            $display .= $row['account_type_name'].' ('.$row['account_last_digits'].')\n'.($row['expense']?'Expense':'Income').($row['confirm']?'\nConfirmed':'');
            $display .= ")\"></span></td>\r\n";
            $display .= '  <td><input type="checkbox" '.($row['has_attachment']==1 ? 'checked' : '' )." onclick=\"return false;\" /></td>\r\n";
            $display .= '  <td>'.$tag_displays."</td>\r\n";
            $display .= "</tr>\r\n";
        }
        return $display;
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

    private static function get_env(){
        return getenv('ENV_TYPE');
    }
    
    public static function get_url(){
        if(self::get_env() == 'live'){
            return 'https://services.jdenoc.com/api/money_tracker';
        } else {
            return 'http://codeigniter.services.local/api/money_tracker';
        }
    }

    public static function upload_attachment($attachments){
        // Attachment uploader
        $md5 = include_once(__DIR__ . '/../config/config.md5.php');
        $output_dir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."receipts_attachments".DIRECTORY_SEPARATOR;
        $tmp_dir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR;
        $has_attachment = 0;
        if(!empty($attachments)){
            foreach($attachments as $attachment){
                $has_attachment = 1;
                $pos = strrpos($attachment, '.');
                $ext = substr($attachment, $pos);
                rename($tmp_dir.$attachment,$output_dir.md5($attachment.$md5).$ext);
            }
        }
        return $has_attachment;
    }

    public static function display_account_settings($account_data){
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

    public static function undo_json_decode($data){
        return json_encode($data);
    }
}