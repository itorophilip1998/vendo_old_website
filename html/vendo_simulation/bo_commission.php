<?php

include_once("translate.php");
include_once("configuration.php");
include_once("utils.php");
include_once("enums.php");


$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

include 'bo_read_user_logged_in.php';
// is logged in

try {


} catch (Exception $e) {
    $msg = "exception: " . $e->getMessage();
    echo $msg;
    error_log($msg);
    die();
}

?>

<html>

<head>
    <title>Vendo Backoffice</title>

    <?php
    echo file_get_contents("bo_head.html");
    ?>
</head>

<body>

    <div id="divToBlur" class="bo_container_all">

        <!-- navbar -->
        <div>
            <?php
            include("bo_navbar.php");
            ?>
        </div>

        <div class="bo_container_content main-color">

            <div class="bo_commission_container_summary">
                <div class="bo_panel flex_flow_row">
                    <div class="col">
                        <div class="row bo_panel_title flex-fill">
                            <div class="col">
                                <?php echo localize("bo_commission_summary_title"); ?>
                            </div>
                        </div>

                        <div class="row flex-fill">

                            <table class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
								<thead class="bo_text_header table_bottom_line">
									<th class="fontBold">
										<?php echo localize("bo_commission_summary_earned"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_commission_summary_on_hold"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_commission_summary_available"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_commission_summary_paid"); ?>
									</th>
								</thead>
								<tbody id="tableBodySummary" class="bo_text_table_body">
                                    
								</tbody>
							</table>
                        </div>
                        <div class="row">
                            <div id="loadingMarqueeSummary" class="loadingOverlayRelative">
                                <img src="./Images/loading.gif" class="loadingGif">
                            </div>
                        </div>

                    </div>

                    <div class="col flex_grow_unset">
                        <input id="buttonPayout" type="button" class="button_filled width100" value="<?php echo localize("bo_commission_button_payout"); ?>"/>
                        <div class="verticalSpacer"></div>
                    </div>

                </div>
            </div>

            <div class="bo_commission_container_commercial">
                <div class="bo_panel">
                </div>
            </div>

            <div class="bo_commission_container_billing">
                <div class="bo_panel" style="min-height: 400px;">
                    <div class="row bo_panel_title">
                        <div class="col">
                            <?php echo localize("bo_commission_billing_title"); ?>
                        </div>
                    </div>

					<div class="row bo_dl_table_members">
						<div class="col">
							<table id="table_commission" class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
								<thead class="bo_text_header table_bottom_line">
									<th class="fontBold">
										<?php echo localize("bo_commission_table_customer"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_commission_table_access"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_commission_table_price"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_commission_table_direct"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_commission_table_type"); ?>
                                    </th>
                                    <th class="fontBold">
										<?php echo localize("bo_commission_table_level"); ?>
									</th>
                                    <th class="fontBold">
										<?php echo localize("bo_commission_table_commission"); ?>
									</th>
                                    <th class="fontBold">
										<?php echo localize("bo_commission_table_amount"); ?>
									</th>
                                    <th class="fontBold">
										<?php echo localize("bo_commission_table_status"); ?>
									</th>

								</thead>
								<tbody id="tableDownlineBody" class="bo_text_table_body">
                                    
								</tbody>
							</table>
                        </div>

                    </div>
                    <div class="row">
                        <div id="loadingMarquee" class="loadingOverlayRelative">
                            <img src="./Images/loading.gif" class="loadingGif">
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>


    <script>
        window.onload = function() {
            document.getElementById("nav_commission").classList.add("active");
        }

        $(document).ready(function() {
            $.post("ajax_init_commission_site.php",{}, function(data) {
                try {
                    var response = JSON.parse(data);
                    if(response.code == 200) {
                        $("#loadingMarquee").hide();
                        $("#tableDownlineBody").html(response.commissions);

                        $("#loadingMarqueeSummary").hide();
                        $("#tableBodySummary").html(response.summary);
                    }

                } catch (error) {
                    console.log(error);
                }
            
            });

            $('#buttonPayout').click(function() {
                window.location.href = "/bo_payout.php";
            });
        });


    </script>

</body>

</html>