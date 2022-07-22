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

            <div class="bo_vq_container_summary">
                <div class="bo_panel flex_flow_row">
                    <div class="col">
                        <div class="row bo_panel_title flex-fill">
                            <div class="col">
                                <?php echo localize("bo_vq_summary_title"); ?>
                            </div>
                        </div>

                        <div class="row flex-fill">
                            <div class="col">
                                <div class="bo_text_normal info_label" ><?php echo localize("bo_vq_summary_sum"); ?></div>
                                <div id="divSum" class="bo_text_big"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div id="loadingMarqueeSummary" class="loadingOverlayRelative">
                                <img src="./Images/loading.gif" class="loadingGif">
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <div class="bo_vq_container_table">
                <div class="bo_panel" style="min-height: 400px;">
                    <div class="row bo_panel_title">
                        <div class="col">
                            <?php echo localize("bo_vq_details_title"); ?>
                        </div>
                    </div>

					<div class="row">
						<div class="col">
							<table id="table_commission" class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
								<thead class="bo_text_header table_bottom_line">
									<th class="fontBold">
										<?php echo localize("bo_vq_table_date"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_vq_table_amount"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_vq_table_type"); ?>
									</th>

								</thead>
								<tbody id="tableVQsBody" class="bo_text_table_body lineHeight100">
                                    
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
            document.getElementById("nav_vq").classList.add("active");
        }

        $(document).ready(function() {
            $.post("ajax_init_vq.php",{}, function(data) {
                try {
                    var response = JSON.parse(data);
                    if(response.code == 200) {
                        $("#loadingMarquee").hide();
                        $("#tableVQsBody").html(response.vqs);

                        $("#loadingMarqueeSummary").hide();
                        $("#divSum").html(response.sum);

                        var cellsTime = document.getElementsByClassName("table_vq_details_cell_time");
                        Array.prototype.forEach.call(cellsTime, function(cell) {
                            var timeUTCText = cell.getAttribute("data-time-utc");
                            var dateMoment = moment.utc(timeUTCText, "YYYY-MM-DD HH:mm:ss");
                            dateMoment.local();
                            var dateText = dateMoment.format("DD.MM.YY");
                            var timeText = dateMoment.format("HH:mm:ss");
                            cell.innerHTML = dateText + "<br><div class=\"secondary-color\">" + timeText + "</div>";
                        });
                    };

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