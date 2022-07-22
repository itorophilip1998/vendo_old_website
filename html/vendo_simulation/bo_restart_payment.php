<?php
include_once("translate.php");
include_once("configuration.php");
include_once("utils.php");
include_once("enums.php");
require_once('bitcoin.php');

try {
	
	

	$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    include 'bo_read_user_logged_in.php';
    
    $payment_id = $_REQUEST['payment_id'];

    $payment = BitCoin::getPaymentInfo($payment_id);

    // hack prevention
    if ($payment['user_id'] != $user["id"])
    {
        //silent fail 
        redirect(ROOT_URL);
    }
    else{
        if ($payment['status'] == 'pending')
        {
            //set old payment to "expired"
            BitCoin::updatePaymentStatus($payment_id, 'expired');
            //start new payment
            $payment_html = BitCoin::getPaymentHtml($user["id"], $payment['product'], $payment['amount'] - $payment['paid_amount_usd'], $payment['currency'], $payment['type'], false, $user['payment_method']);
        }
        else if ($payment['status'] == 'expired') //already restarted (double click)
        {
            //recall new payment
            $payment_html = BitCoin::getPaymentHtml($user["id"], $payment['product'], $payment['amount'] - $payment['paid_amount_usd'], $payment['currency'], $payment['type'], 'force_reuse', $user['payment_method']);

            if ($payment_html === false)
            {
                redirect(ROOT_URL.'bo_main.php');
            }
        }
        else // complete
        {
            redirect(ROOT_URL.'bo_main.php');
        }
    }

} catch (Exception $e) {
	error_log($msg);
}

?>
<!DOCTYPE html>
<html>

    <head>
        

        <?php
        echo file_get_contents("bo_head.html");
        ?>
    </head>

    <body>

        <div id="divToBlur" class="bo_container_all reg_container_for_blur">
            <!-- navbar -->
            <div>
                <?php
                include("bo_navbar.php");
                ?>
            </div>


            <div class="main-color container fit-content">
                <div class="verticalSpacer"></div>
                <div class="verticalSpacer"></div>

                <?php echo $payment_html; ?>

                <div class="verticalSpacer"></div>
                <div class="verticalSpacer"></div>

                <div class="">
                    <a href="/bo_main.php" id="buttonContinue" type="button" role="button" class="btn button_filled"><?php echo localize("general_reg_complete"); ?></a>
                </div>            
            </div>


        </div>
    </body>

</html>