<?php
include_once("translate.php");
include_once("configuration.php");
include_once("db.php");
include_once("utils.php");
include_once("enums.php");
require_once('bitcoin.php');
require_once('lib/commissions.php');

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    include 'bo_read_user_logged_in.php';

    $style_hide = 'style="display: none !important;"';
    $action = isset($_POST['action'])?$_POST['action']:false;

    $commissions_calculator = new Commissions;
    $commission_balance = $commissions_calculator->getBalance($user['id'], Commissions::COMMISSION_CURRENCY_USD, Commissions::COMMISSION_STATUS_CONFIRMED, Commissions::COMMISSION_PAYOUT_OPEN, /*out*/ $commission_ids);

    $show_error_address = false;
    $commit_payment = true;
    if ($action === 'submit')
    {
        $address = $_POST['payout_address'];        
    

        if (empty($address))
        {
            $show_error_address = true;
            $commit_payment = false;
        }

        if ($commit_payment)
        {
            $transaction = Bitcoin::startBitcoinWithdrawal($user['id'], $commission_balance, $address);            

            if ($transaction)
            {
                $payout_committed_top = localize("bo_payout_committed_top");
                $payout_details = localize("bo_payout_details");

                $payout_details = str_replace(":amount", number_format($transaction->amount, 2, ",", "."), $payout_details);
                $payout_details = str_replace(":address", $address, $payout_details);

                $commissions_calculator->setCommissionsPaid($user['id'], $commission_ids);
            }
            else
            {
                $payout_committed_top = localize("bo_payout_error");
                $payout_details = localize("bo_payout_error_details");
            }
        }

    }
    else{
        $balance_text = localize("bo_payout_balance");

        $balance_text = str_replace(":amount", number_format($commission_balance, 2, ",", "."), $balance_text);
    }
    


} catch (Exception $e) {
	error_log($e->getMessage());
}

?>

<!DOCTYPE html>
<html>

    <head>
        <?php include("bo_head.html");  ?>
    </head>

    <body>

        <div id="divToBlur" class="bo_container_all reg_container_for_blur">
            <!-- navbar -->
            <div>
                <?php include("bo_navbar.php"); ?>
            </div>


            <div class="main-color container fit-content" id="bo_payout">
                <?php if ($action === 'submit'): ?>
                    <div class="verticalSpacer"></div>
                    <div class="verticalSpacer"></div>

                    <div class="textLarger">
                        <?php echo $payout_committed_top; ?>
                    </div>

                    <div class="verticalSpacer"></div>

                    <div class="textLarger">
                        <?php echo $payout_details; ?>
                    </div>
                <?php else: ?> 
                    <div class="verticalSpacer"></div>
                    <div class="verticalSpacer"></div>

                    <div class="textLarger">
                        <?php echo localize("bo_payout_top"); ?>
                    </div>

                    <div class="verticalSpacer"></div>
                    <form id="payoutForm" action="" method="post">
                        <div class="textLarger">
                            <?php echo $balance_text; ?>
                        </div>	

                        <div class="verticalSpacer"></div>
                        <div class="verticalSpacer"></div>

                        <div class="textNormal">
                            <?php echo localize("bo_payout_wallet_address"); ?>
                        </div>	
                        <div>
                            <input id="payout_address" type="text" name="payout_address" value="<?= $user["payout_address"] ?>">
                            <div id="empty_address" class="d-flex flex-row red" <?php if(!$show_error_address) echo $style_hide; ?> ><?= localize('bo_payout_empty_address'); ?></div>
                        </div>

                        <div class="verticalSpacer"></div>
                        <div class="verticalSpacer"></div>

                        <div class="">
                            <input type="hidden" name="action" value="submit"/>
                            <input type="submit" id="buttonContinue" type="button" role="button" class="btn button_filled" value="<?php echo localize("bo_payout_button"); ?>"/>                    
                        </div>        
                    </form>  
                <?php endif; ?> 
            </div>


        </div>

        <script>
            $(function() {
                $("#payoutForm").submit(function(e) {
                    
                    $('#empty_address').hide();

                    address = $('input#payout_address').val();


                    if (address === '')
                    {
                        $('#empty_address').show();
                        e.preventDefault();
                        return false;
                    }

                    //else continue submit
                    return true;
                });
            });
        </script>
    </body>

</html>