<?php
    include("configuration.php");
    include("db.php");
    include("translate.php");
    include("utils.php");

    session_start(); // start or continue a session
    $loggedInAsSponsorId = $_SESSION["loggedInAsUserId"];
    if (!$loggedInAsSponsorId) {
        $result["message"] = "Your session has expired, please log in again.";
        $result["code"] = -10;
        $jsonOut=json_encode($result);		
        die($jsonOut);            
    }
    
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    include 'bo_read_user_logged_in.php';
    // is logged in

    /* Getting file name */
    $newPicture = $_FILES['newProfilePicture'];

    if(isset($newPicture)) {
        if($newPicture['error'] == UPLOAD_ERR_OK) {

            if($newPicture['type'] ==  "image/jpeg" || $newPicture['type'] == "image/png" || $newPicture['type'] == "image/gif") {
    
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_';
                $ext = pathinfo($newPicture['name'], PATHINFO_EXTENSION);
                
                $filename = $user['id'] . substr(str_shuffle($permitted_chars), 0, 50) . ".$ext";
                
                if(!is_dir(PROFILE_PICTURE_ROOT_DIR . $user['id'] . PICTURE_DIR)) {
                    mkdir(PROFILE_PICTURE_ROOT_DIR . $user['id'] . PICTURE_DIR, 0777, true);
                }
    
                /* Location */
                $location = PROFILE_PICTURE_ROOT_DIR . $user['id'] . PICTURE_DIR . $filename; 
    
                /* Upload file */
                if(move_uploaded_file($newPicture['tmp_name'], $location)){
                    if($user['profile_picture_name']) {
                        unlink(PROFILE_PICTURE_ROOT_DIR . $user['id'] . PICTURE_DIR . $user['profile_picture_name']);
                    }
        
                    updateUser($pdo, $user['id'], [
                        'profile_picture_name' => $filename
                    ]);
    
                    echo json_encode([
                        'data' => "<div class=\"profile_image_background mb-4\"><img src=\"". $location . "\" alt=\"no valid profile image\"></img></div>",
                        'code' => 200
                    ]);
                }  else {
                    echo json_encode([
                        'message' => localize('bo_profile_error_file_could_not_be_uploaded', $user['language']),
                        'code' => -2
                    ]);
                }
            } else {
                echo json_encode([
                    'message' => localize('bo_profile_error_invalid_file_type', $user['language']),
                    'code' => -2
                ]);
            }
        } else {
            if($newPicture['error'] == UPLOAD_ERR_INI_SIZE) {
                error_log("file size to much");
                $text = localize('bo_profile_error_invalid_file_size', $user['language']);
                $text = str_replace(":size", file_upload_max_size() / 1024 / 1024 . " MB", $text);
    
                echo json_encode([
                    'message' => $text,
                    'code' => -2
                ]);
            } else {
                $text = localize('bo_error_unknown', $user['language']);
                echo json_encode([
                    'message' => $text,
                    'code' => -2
                ]);
            }
        }
    } else {
        $text = localize('bo_profile_error_no_file_chosen', $user['language']);
        $text = str_replace(":size", parse_size(ini_get('post_max_size')) / 1024 / 1024 . " MB", $text);
        echo json_encode([
            'message' => $text,
            'code' => -2
        ]);
 
    }
    

?>