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
    $balance = $user['balance'];

    $closed_trades_P_L = summarizeClosedTradesPL($pdo, $user['id']);
    $deposit_withdrawal = summarizeDepositWithdrawal($pdo, $user['id']);
    $performance_fee = summarizePerformanceFee($pdo, $user['id']);

    $prev_balance = 25444.45; //FUTURE: calculate/get proper value 

    $orderHistory = readOrderHistory($pdo, $user["id"]);

    foreach($orderHistory as $order) {
        // $order["open_time"];
    }


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

        <div class="bo_container_content bo_container_content_history main-color">

            <div class="bo_history_container_summary">
                <div class="bo_panel bo_commission_container_summary_panel">
                    <div class="row bo_panel_title flex-fill">
						<div class="col">
                            <?php echo localize("bo_history_summary_title"); ?>
						</div>
					</div>

					<div class="bo_separator"></div>
					<div class="row flex-fill">
						<div class="col">
                        <div class="bo_text_normal break_whole_div" style="width:55%"><?php echo localize("bo_history_summary_profit"); ?></div><div class="bo_text_big"><?php echo number_format($closed_trades_P_L, 2, '.', ',');?></div>
						</div>
					</div>
					<div class="bo_separator"></div>
					<div class="row flex-fill">
						<div class="col">
                        <div class="bo_text_normal break_whole_div" style="width:55%"><?php echo localize("bo_history_summary_deposit"); ?></div><div class="bo_text_big"><?php echo number_format($deposit_withdrawal, 2, '.', ',');?></div>
						</div>
					</div>
					<div class="bo_separator"></div>
					<div class="row flex-fill">
						<div class="col">
                        <div class="bo_text_normal break_whole_div" style="width:55%"><?php echo localize("bo_history_summary_fee"); ?></div><div class="bo_text_big"><?php echo number_format($performance_fee, 2, '.', ',');?></div>
						</div>
                    </div>					
                    <div class="bo_seperator_sidenav"></div>
					<div class="row flex-fill">
						<div class="col">
                        <div class="bo_text_normal break_whole_div" style="width:55%"><?php echo localize("bo_history_summary_balance"); ?></div><div class="bo_text_big"><?php echo number_format($balance, 2, '.', ',');?></div>
						</div>
					</div>

                </div>
            </div>

            <div class="bo_history_container_closed_transactions">
                <div class="bo_panel" style="min-height: 600px; display: block;">
                    <div class="row bo_panel_title">
                        <div class="col">
                            <div style="display: inline;">
                                <?php echo localize("bo_history_closed_transactions_title"); ?>
                            </div>

                            <div style="display: inline; float: right;">
                                <select id="selectGrouping" class="pointer">
                                    <option value="single" class="colorText"><?php echo localize("bo_history_grouping_single"); ?></option>
                                    <option value="days" class="colorText"><?php echo localize("bo_history_grouping_days"); ?></option>
                                    <option value="months" class="colorText"><?php echo localize("bo_history_grouping_months"); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

					<div class="row bo_history_table_closed_transactions">
						<div class="col">
							<table id="table_history_orders" class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
								<thead class="bo_text_header table_bottom_line">
									<th class="fontBold">
										<?php echo localize("bo_history_table_ticket"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_history_table_open_time"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_history_table_profit"); ?>
									</th>
								</thead>
								<tbody id="tableHistoryBody" class="bo_text_table_body lineHeight100">
                                    <?php foreach($orderHistory as $order): ?>
                                        <tr>
                                            <td>
                                                <?php echo $order["order"] ?>
                                            </td>
                                            <td class="table_closed_transactions_cell_time" 
                                                data-time-utc="<?php echo $order["open_time"] ?>" 
                                                data-profit="<?php echo $order["profit"] ?>">                                                
                                            </td>
                                            <td class="<?php if($order["profit"] > 0) echo "green"; else if($order["profit"] < 0) echo "red";?>">
                                                <?php echo $order["profit"] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
								</tbody>
							</table>

                            <table id="table_history_orders_days" class="hidden table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
								<thead class="bo_text_header table_bottom_line">
									<th class="fontBold">
										<?php echo localize("bo_history_table_days_open_time"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_history_table_days_profit"); ?>
									</th>
								</thead>
								<tbody id="tableHistoryDaysBody" class="bo_text_table_body lineHeight100">
								</tbody>
							</table>

							<table id="table_history_orders_months" class="hidden table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
								<thead class="bo_text_header table_bottom_line">
									<th class="fontBold">
										<?php echo localize("bo_history_table_months_open_time"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_history_table_months_profit"); ?>
									</th>
								</thead>
								<tbody id="tableHistoryMonthsBody" class="bo_text_table_body lineHeight100">
								</tbody>
							</table>

						</div>
					</div>

                </div>
            </div>

        </div>
    </div>


    <script>
        window.onload = function() {
            //var dateMoment = moment.utc(codesInfo[i]["date"], "YYYY-MM-DD HH:mm:ss");
            //var dateText = dateMoment.local().format("DD.MM.YYYY");

            const mapMonths = new Map();
            const mapDays = new Map();

            var cellsTime = document.getElementsByClassName("table_closed_transactions_cell_time");
            Array.prototype.forEach.call(cellsTime, function(cell) {
                var timeUTCText = cell.getAttribute("data-time-utc");
                var dateMoment = moment.utc(timeUTCText, "YYYY-MM-DD HH:mm:ss");
                dateMoment.local();
                var dateText = dateMoment.format("DD.MM.YY");
                var timeText = dateMoment.format("HH:mm:ss");
                cell.innerHTML = dateText + "<br><div class=\"secondary-color\">" + timeText + "</div>";

                // collect profit data
                var profit = parseFloat(cell.getAttribute("data-profit"));

                document.querySelectorAll('[property]');
                var keyMapDays = dateMoment.format("YYYY-MM-DD");
                var profitDay = 0.0;
                if(mapDays.has(keyMapDays))
                    profitDay=mapDays.get(keyMapDays);
                profitDay = profitDay + profit;
                mapDays.set(keyMapDays, profitDay)

                var keyMapMonths = dateMoment.format("YYYY-MM");
                var profitMonth = 0.0;
                if(mapMonths.has(keyMapMonths))
                    profitMonth = mapMonths.get(keyMapMonths);
                profitMonth = profitMonth + profit;
                mapMonths.set(keyMapMonths, profitMonth)
            });

            // create table days
            var tableBodyDays = document.getElementById("tableHistoryDaysBody");
            Map.prototype.forEach.call(mapDays, function(profit, keyDay, map) {
                console.log(keyDay + ": " + profit);

                var tableRow = document.createElement("tr");
                
                var tableCell0 = document.createElement("td");
                var momentDay = moment(keyDay);
                tableCell0.innerHTML = momentDay.format("DD.MM.YY")
                tableRow.appendChild(tableCell0);
                
                var tableCell1 = document.createElement("td");
                tableCell1.innerHTML = Math.round((profit + Number.EPSILON) * 100) / 100;
                if(profit > 0.0)
                    tableCell1.classList.add("green")
                else if(profit < 0.0)
                    tableCell1.classList.add("red")
                tableRow.appendChild(tableCell1);

                tableBodyDays.appendChild(tableRow);
            })

            // create table months
            var tableBodyMonths = document.getElementById("tableHistoryMonthsBody");
            Map.prototype.forEach.call(mapMonths, function(profit, keyMonth, map) {
                console.log(keyMonth + ": " + profit);
                
                var tableRow = document.createElement("tr");
                
                var tableCell0 = document.createElement("td");
                var momentMonth = moment(keyMonth);
                tableCell0.innerHTML = momentMonth.format("MM.YY")
                tableRow.appendChild(tableCell0);
                
                var tableCell1 = document.createElement("td");
                tableCell1.innerHTML = Math.round((profit + Number.EPSILON) * 100) / 100;
                if(profit > 0.0)
                    tableCell1.classList.add("green")
                else if(profit < 0.0)
                    tableCell1.classList.add("red")
                tableRow.appendChild(tableCell1);

                tableBodyMonths.appendChild(tableRow);
            })

            // handle order grouping selection
            document.getElementById("selectGrouping").addEventListener("change", function() {
				var nameGrouping = document.getElementById("selectGrouping").value;

                document.getElementById("table_history_orders").classList.add("hidden")
                document.getElementById("table_history_orders_days").classList.add("hidden")
                document.getElementById("table_history_orders_months").classList.add("hidden")

                if(nameGrouping == "single") {
                    document.getElementById("table_history_orders").classList.remove("hidden")
                } else if(nameGrouping == "days") {
                    document.getElementById("table_history_orders_days").classList.remove("hidden")
                } else if(nameGrouping == "months") {
                    document.getElementById("table_history_orders_months").classList.remove("hidden")
                }
            });

            document.getElementById("nav_history").classList.add("active");
        }
    </script>

</body>

</html>+