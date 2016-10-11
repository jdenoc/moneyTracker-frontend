<?php
/**
 * Used to handle initial file uploads, so we don't loose the file after the upload session is complete.
 */

if(isset($_FILES["file"])){
//Filter the file types , if you want.
    if ($_FILES["file"]["error"] > 0){
        echo "Error: " . $_FILES["file"]["error"];
    } else {
        move_uploaded_file($_FILES["file"]["tmp_name"], sys_get_temp_dir().DIRECTORY_SEPARATOR.str_replace(' ', '_', $_FILES["file"]["name"]));
        echo json_encode(str_replace(' ', '_', $_FILES["file"]["name"]));
    }
}