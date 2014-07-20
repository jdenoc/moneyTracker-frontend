<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 3/7/14
 * Time: 12:00 AM
 */

$tmp_dir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR;
if(isset($_FILES["file"])){
//Filter the file types , if you want.
    if ($_FILES["file"]["error"] > 0){
        echo "Error: " . $_FILES["file"]["error"];
    } else {
        move_uploaded_file($_FILES["file"]["tmp_name"], $tmp_dir.str_replace(' ', '_', $_FILES["file"]["name"]));
        echo json_encode(str_replace(' ', '_', $_FILES["file"]["name"]));
    }

}