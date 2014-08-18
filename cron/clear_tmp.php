<?php
/**
 * User: denis
 * Date: 2014-08-17
 */

$files = glob(__DIR__.'/../tmp/*');
$now = time();
$two_hours = 2*60*60;

foreach($files as $tmp_file){
    if(is_file($tmp_file)){
        if($now - filemtime($tmp_file) >= $two_hours){
            unlink($tmp_file);
        }
    }
}