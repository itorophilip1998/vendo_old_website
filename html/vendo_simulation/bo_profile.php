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

// $user = [
//     'language' => 'de',
//     'given_name' => 'Marcel',
//     'sur_name' => 'Sotiropoulos',
//     'trading_account' => 1,
//     'payment_method' => 1,
//     'date_of_birth' => "1998-01-01",
//     'street' => 'Am Arlandgrund',
//     'postcode' => '8045',
//     'sex' => 1,
//     'city' => 'Graz',
//     'country' => 'at',
//     'mobile_number' => '06801313043',
//     'email' => 'marcel.sotiropoulos@soti-it.at'
// ];

$profileGreeting = localize('bo_profile_greetings', $user['language']);
$profileGreeting = str_replace(':Name', $user['given_name'], $profileGreeting);

$profileEdit = localize('bo_profile_edit', $user['language']);
$profilePersonalInformationHeader = localize('bo_profile_personal_information_header', $user['language']);
$profileAccountInformationHeader = localize('bo_profile_header_account_information', $user['language']);

$profileAccess = localize('bo_profile_access', $user['language']);

$profilePaymentMethod = localize('bo_profile_payment_method', $user['language']);
$userPaymentMethod = getPaymentMethodName($user['payment_method'], $user['language']);
$profileAddress = localize('bo_profile_address', $user['language']);
$profileContactInformation = localize('bo_profile_contact_information', $user['language']);

$useraccess = getNameTradingAccount($user['trading_account']);
$country = getCountryByIso($pdo, $user['country'], $user['language']);
$countryPhoneCode = getCountryByIso($pdo, $user['phonecode'], $user['language']);

?>

<html>

<head>

    <?php
    echo file_get_contents("bo_head.html");
    ?>
</head>

<body>
    <div class="profile_container_all">
        <div class="profile_container_left">
            <div class="bo_profile_panel_logo_logo">
                <a href="./bo_main.php"><img src='Images/logo1.png' height=60 accept="image/*"></img></a>
            </div>
            
            <div id="greetingText" class="panel_logo_text">
                <?= $profileGreeting ?>
            </div>
        </div>

        <div class="profile_container_right">
            <div id="divToBlur" class="d-flex flex-column flex-fill">
                <div class="d-flex-reverse flex-fill">
                    <div class="text-right align-self-center beige">
                        <!--
                        <span style="font-size:30px;cursor:pointer;text-align: right;" onclick="openNav()">&#9776;</span>
-->
                    </div>
                </div>
                <div id="loadingMarquee" class="mb-4" style="display: none;">
                    <div class="profile_image_background ">
                        <img src="./Images/loading.gif" alt="" style="width: 70px; height: 70px; min-width: 70px !important; max-height: 70px !important; max-width: 70px !important; min-height: 70px !important; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                    </div>
                </div>
                <div id="profileImage">
                    <div class="profile_image_background mb-4">
                        <?php if($user['profile_picture_name']): ?>
                        <img src="<?= PROFILE_PICTURE_ROOT_DIR . $user['id'] . PICTURE_DIR . $user['profile_picture_name'] ?>" alt="no valid profile image"></img>
                        <?php else: ?>
                            <img src="Images/profile.jpg" class="rounded-circle bo_profile_image">
                        <?php endif; ?>
                    </div>
                </div>
                <form enctype="multipart/form-data"> 
                    <div id="uploadProfilePictureButton" class="profile_image_edit_background pointer">
                        <img class="profile_image_edit_pencil" src="./Images/editPencil.png" alt="">
                    </div>
                    <input class="profile_image_edit_pencil" style="display: none" type="file" name="fileToUpload" id="fileToUpload" required>
                </form>

                <div class="flex-fill mb-4">
                    <div class="d-flex flex-row bd-highlight">
                        <div class="flex-grow-1 bd-highlight bo_text_small"><?= $profileAccountInformationHeader ?></div>
                        <div id="editAccountInformation" class="bd-highlight bo_profile_title_edit pointer"><?= $profileEdit ?></div>
                    </div>
                    <div class="bo_separator"></div>
                    <div class="verticalSpacerS"></div>
                    <div class="d-flex flex-column bd-highlight">
                        <div id="accountInformation">
                            <?php include("./html_templates/profile/readonlyAccountInformation.php"); ?>
                        </div>
                    </div>
                </div>
                <!-- <div class="flex-fill mb-4">
                    <div class="d-flex flex-row bd-highlight">
                        <div class="flex-grow-1 bd-highlight bo_text_small"><?= ""//$profilePaymentMethod ?></div>
                        <div class="bd-highlight bo_profile_title_edit pointer"><?= ""//$profileEdit ?></div>
                    </div>
                    <div class="bo_separator"></div>
                    <div class="verticalSpacerS"></div>
                    <div class="d-flex flex-column bd-highlight">
                        <div class="bo_profile_title_edit">
                            <?= ""// $userPaymentMethod ?>
                        </div>
                    </div>
                </div> -->

                <div class="flex-fill mb-4">
                    <div class="d-flex flex-row bd-highlight">
                        <div class="flex-grow-1 bd-highlight bo_text_small"><?= $profilePersonalInformationHeader ?></div>
                        <div id="editPersonalInformation" class="bd-highlight bo_profile_title_edit pointer"><?= $profileEdit ?></div>
                    </div>
                    <div class="bo_separator"></div>
                    <div class="verticalSpacerS"></div>
                    <div class="d-flex flex-column bd-highlight">
                        <div id="personalInformation">
                            <?php include("./html_templates/profile/readonlyPersonalInformation.php"); ?>
                        </div>
                    </div>
                </div>

                <div class="flex-fill mb-4">
                    <div class="d-flex flex-row bd-highlight">
                        <div class="flex-grow-1 bd-highlight bo_text_small"><?= $profileAddress ?></div>
                        <div id="editPersonalAdress" class="bd-highlight bo_profile_title_edit pointer"><?= $profileEdit ?></div>
                    </div>
                    <div class="bo_separator"></div>
                    <div class="verticalSpacerS"></div>
                    <div class="d-flex flex-column bd-highlight">
                        <div id="profileAdress">
                            <?php include("./html_templates/profile/readonlyPersonalAdress.php"); ?>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <div id="resetPasswordDialog" class="profile_modal_change_password" style="display: none">
                <!-- Modal content -->
                <div class="profile_modal_change_password_content">
                    <div class="text_modal mb-5">
                     <?= localize("bo_profile_reset_password_header", $user['language']) ?>
                    </div>
                    <div class="bo_profile_text mb-5">
                        <?= localize('bo_profile_reset_password_text', $user['language']) ?>
                    </div>
                    <button id="buttonResetPasswordOK" class="div_modal_warning_close button_filled inline button_text_modal_black">OK</button>
                </div>
            </div>

            <div id="passwordForm"></div>
        </div>
    </div>
    
    <script src="./lib/js/bo_profile.js"></script>
</body>