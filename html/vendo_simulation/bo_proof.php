<?php
include_once("translate.php");
include_once("configuration.php");
include_once("utils.php");
include_once("enums.php");
include_once("lib/commissions.php");

require_once("./lib/protoncapitalmarkets.php");

use Brokers\ProtonCapitalMarketsBroker;
use Brokers\ProtonCapitalMarketsException;

$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

include 'bo_read_user_logged_in.php';
// is logged in



?>

<!DOCTYPE html>
<html>

<head>


    <?php
    echo file_get_contents("bo_head.html");
    ?>

    <link rel="stylesheet" type="text/css" href="./lib/ChartJS/Chart.css">
    </link>
</head>

<body>

    <div id="divToBlur" class="bo_container_all reg_container_for_blur">
        <!-- navbar -->
        <div>
            <?php
            include("bo_navbar.php");
            ?>
        </div>
        <div class="bo_proof_container main-color">
            <div class="bo_proof_chart_container">
                <div class="bo_panel">
                    <div id="loadingChartMarquee" class="loadingOverlayRelative">
                        <img src="./Images/loading.gif" class="loadingGif">
                    </div>
                    <div class="row bo_panel_title mb-4">
                        <div class="col">
                            <?= localize('bo_proof_profit_header', $user['language']) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div style="position: relative; width: 100%; height: 300px;">
                            <canvas id="profitPerMonthChart" class="chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bo_proof_risk_container">
                <div class="bo_panel">
                    <div class="row bo_panel_title mb-4">
                        <div class="col">
                            <?= localize('bo_proof_risk_header', $user['language']) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead class="secondary-color bo_text_header table_bottom_line">
                                    <tr>
                                        <th class="text-left"><?= localize('bo_weekly_return', $user['language']) ?></th>
                                        <th class="text-left"><?= localize('bo_monthly_return', $user['language']) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="">
                                        <td class="banked_profits_winning text-left">12</td>
                                        <td class="banked_profits_winning text-left">34</td>
                                    </tr>
                                </tbody>
                                <thead class="secondary-color bo_text_header table_bottom_line">
                                    <tr>
                                        <th class="text-left"><?= localize('bo_worst_week', $user['language']) ?></th>
                                        <th class="text-left"><?= localize('bo_worst_month', $user['language']) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="">
                                        <td class="banked_profits_losing text-left">12</td>
                                        <td class="banked_profits_losing text-left">13</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bo_proof_trades_container">
                <div class="bo_panel">
                    <div class="row bo_panel_title mb-4">
                        <div class="col">
                            <?= localize('bo_proof_trades_header', $user['language']) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr class="bo_proof_border_bottom">
                                        <td class="bo_profile_text align-middle"><?= localize('bo_average_trade_length', $user['language']) ?></td>
                                        <td class="bo_proof_table_trades_big_text align-middle">23</td>
                                    </tr>
                                    <tr class="bo_proof_border_bottom_light">
                                        <td class="bo_profile_text align-middle"><?= localize('bo_trades_per_day', $user['language']) ?></td>
                                        <td class="bo_proof_table_trades_big_text align-middle">2</td>
                                    </tr>
                                    <tr class="">
                                        <td class="bo_profile_text align-middle"><?= localize('bo_monthly_return', $user['language']) ?></td>
                                        <td class="bo_proof_table_trades_big_text_green align-middle">12</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bo_proof_banked_profits_container">
                <div class="bo_panel">
                    <div id="loadingBankedProfitsMarquee" class="loadingOverlayRelative">
                        <img src="./Images/loading.gif" class="loadingGif">
                    </div>

                    <div class="row bo_panel_title mb-4">
                        <div class="col">
                            <?= localize('bo_proof_banked_profits_header', $user['language']) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead class="secondary-color bo_text_header table_bottom_line">
                                    <tr>
                                        <th></th>
                                        <th><?= localize('bo_winning', $user['language']) ?></th>
                                        <th><?= localize('bo_losing', $user['language']) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bo_proof_border_bottom">
                                        <td class="bo_profile_text"><?= localize('bo_days', $user['language']) ?></td>
                                        <td class="banked_profits_winning" id="daysWin"></td>
                                        <td class="banked_profits_losing" id="daysLose"></td>
                                    </tr>
                                    <tr class="bo_proof_border_bottom">
                                        <td class="bo_profile_text"><?= localize('bo_weeks', $user['language']) ?></td>
                                        <td class="banked_profits_winning" id="weeksWin"></td>
                                        <td class="banked_profits_losing" id="weeksLose"></td>
                                    </tr>
                                    <tr class="bo_proof_border_bottom">
                                        <td class="bo_profile_text"><?= localize('bo_months', $user['language']) ?></td>
                                        <td class="banked_profits_winning" id="monthsWin"></td>
                                        <td class="banked_profits_losing" id="monthsLose"></td>
                                    </tr>
                                    <tr class="bo_proof_border_bottom">
                                        <td class="bo_profile_text"><?= localize('bo_closed_trades', $user['language']) ?></td>
                                        <td class="banked_profits_winning" id="closedTradesWin"></td>
                                        <td class="banked_profits_losing" id="closedTradesLose"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bo_proof_open_positions_container">
                <div class="bo_panel">
                    <div class="row bo_panel_title mb-4">
                        <div class="col">
                            <?= localize('bo_proof_positions_header', $user['language']) ?>
                        </div>
                    </div>
                    <div id="loadingOpenPositionsMarquee" class="row">
                        <div class="loadingOverlayRelative">
                            <img src="./Images/loading.gif" class="loadingGif">
                        </div>
                    </div>

                    <div class="row bo_main_table_codes">
                        <div id="openOrdersTable" class="table-responsive" style="display: none;">
                            <table class="table table-borderless">
                                <thead class="secondary-color bo_proof_table_header table_bottom_line">
                                    <tr>
                                        <th><?= localize('bo_time', $user['language']) ?></th>
                                        <th><?= localize('bo_type', $user['language']) ?></th>
                                        <th><?= localize('bo_symbol', $user['language']) ?></th>
                                        <th><?= localize('bo_price', $user['language']) ?></th>
                                        <!-- <th><?= localize('bo_price', $user['language']) ?></th> -->
                                        <!-- <th><?= localize('bo_pips', $user['language']) ?></th> -->
                                    </tr>
                                </thead>
                                <tbody id="openPositions">
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="./lib/ChartJS/Chart.js"></script>
    <script src="./lib/ChartJS/Chart.roundedBarCharts.js"></script>
    <script>
        var myChart = null;
        $(document).ready(function() {
            $("#nav_proof").addClass("active");


            $.post("ajax_init_proof.php", {}, function(data) {
                try {
                    var response = JSON.parse(data);
                    if (response.code == 200) {
                        var CurrentLabels = response.labels.reverse();
                        var CurrentData = response.data.reverse();

                        var ctx = document.getElementById('profitPerMonthChart').getContext('2d');
                        myChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: CurrentLabels,
                                datasets: [{
                                    data: CurrentData,
                                    backgroundColor: '#DEA36D',
                                    borderColor: '#DEA36D',
                                    borderWidth: 1,
                                    barThickness: 9,
                                    maxBarThickness: 9
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cornerRadius: 4,
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero: true,
                                            fontFamily: 'Arboria'
                                        }
                                    }],
                                    xAxes: [{
                                        ticks: {
                                            fontFamily: 'Arboria'
                                        }
                                    }]
                                },
                                legend: {
                                    display: false
                                },
                                tooltips: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            return tooltipItem.yLabel;
                                        }
                                    }
                                }
                            }
                        });
                        $("#loadingChartMarquee").hide();

                        $("#daysWin").html(response.statistics.daily_profit.winning);
                        $("#daysLose").html(response.statistics.daily_profit.losing);
                        $("#weeksWin").html(response.statistics.weekly_profit.winning);
                        $("#weeksLose").html(response.statistics.weekly_profit.losing);
                        $("#monthsWin").html(response.statistics.monthly_profit.winning);
                        $("#monthsLose").html(response.statistics.monthly_profit.losing);
                        $("#closedTradesWin").html(response.statistics.closedOrdersProfit.winning);
                        $("#closedTradesLose").html(response.statistics.closedOrdersProfit.losing);
                        $("#loadingBankedProfitsMarquee").hide();

                        $("#openPositions").html(response.openOrderRows);
                        $("#loadingOpenPositionsMarquee").hide();
                        $("#openOrdersTable").show();

                    }
                } catch (error) {

                }


            });
        });
    </script>
</body>

</html>