<?php
try {
    include_once("translate.php");
    include_once("configuration.php");
    include_once("utils.php");
    include_once("enums.php");

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    include 'bo_read_user_logged_in.php';
    // is logged in

} catch (Exception $e) {
    $msg = "exception: " . $e->getMessage();
    echo $msg;
    error_log($msg);
    die();
}

$language = strtolower($user['language']);

setlocale(LC_TIME,  getLocale(strtolower($language)));

$effectiveSince = localize('bo_active_since', $language);
$effectiveSince = str_replace(":date", strftime("%B %d, %Y", mktime(0,0,0,1,1,2020)), $effectiveSince);

setlocale(LC_TIME, NULL);

$filename = "html_templates/privacy_policy_$language.html";
if(file_exists($filename)) {
    $fileContents = file_get_contents($filename);
} else {
    $fileContents = file_get_contents("html_templates/privacy_policy_en.html");
}

?>

<html>

<head>

    <?php
    echo file_get_contents("bo_head.html");
    ?>
</head>

<body style="overflow: hidden;">
    <div class="profile_container_all">
        <div class="profile_container_left">
            <div class="bo_profile_panel_logo_logo">
                <a href="./bo_main.php"><img src='Images/logo1.png' height=60 accept="image/*"></img></a>
            </div>

            <div class="panel_logo_text">
                <?= localize('bo_sidebar_privacy_policy', $language) ?>
            </div>
            <div class="panel_logo_text_subheader">
                <?= $effectiveSince ?>
            </div>
        </div>

        <div class="profile_container_right" style="height: 100vh; overflow: auto;">
            <a href="javascript:void(0)" class="closebtn beige" onclick="goBack()">&times;</a>
            <div class="d-flex flex-column flex-fill">
                <div class="d-flex-reverse flex-fill">
                   
                </div>

                <div class="flex-fill mb-4">
                    <div class="verticalSpacerS"></div>
                    <div class="d-flex flex-column bd-highlight">
                        <div style="color: white;">
                            <?= $fileContents ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
            function goBack() {
                window.history.back();
            }
        </script>

</body>