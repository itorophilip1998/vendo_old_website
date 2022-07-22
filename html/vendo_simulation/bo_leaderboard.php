<?php

include_once("translate.php");
include_once("configuration.php");
include_once("utils.php");
include_once("enums.php");
require_once('lib/commissions.php');

$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

include 'bo_read_user_logged_in.php';
// is logged in

try {
} catch (Exception $e) {
    $msg = "exception: " . $e->getMessage();
    error_log($msg);
}

?>

<html>

<head>


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

        <div class="bo_container_content bo_container_content_dl main-color">

            <div class="bo_leaderboard_container_your">
                <div class="bo_panel">
                    <div class="row bo_panel_title flex-fill">
                        <div class="col">
                            Your Position
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <table class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
                                <thead class="bo_text_header table_bottom_line">
                                    <th class="fontBold">
                                        <?php echo localize("bo_leaderboard_table_own_pos"); ?>
                                    </th>
                                    <th class="fontBold">
                                        <?php echo localize("bo_leaderboard_table_own_partner"); ?>
                                    </th>
                                    <th class="fontBold">
                                        <?php echo localize("bo_leaderboard_table_own_rank"); ?>
                                    </th>
                                </thead>
                                <tbody id="tableOwnBody" class="bo_text_table_body">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div id="loadingMarqueeYour" class="loadingOverlayRelative">
                            <img src="./Images/loading.gif" class="loadingGif">
                        </div>
                    </div>

                </div>
            </div>

            <div class="bo_leaderboard_container_all">
                <div class="bo_panel" style="min-height: 600px;">
                    <div class="row bo_panel_title">
                        <div class="col">
                            Leaderboard
                        </div>
                    </div>

                    <div class="row bo_dl_table_members___">
                        <div class="col">
                            <table class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent; margin-bottom: 0;">
                                <thead class="bo_text_header table_bottom_line">
                                    <th class="fontBold">
                                        <?php echo localize("bo_leaderboard_table_all_pos"); ?>
                                    </th>
                                    <th class="fontBold">
                                        <?php echo localize("bo_leaderboard_table_all_partner"); ?>
                                    </th>
                                    <th class="fontBold">
                                        <?php echo localize("bo_leaderboard_table_all_rank"); ?>
                                    </th>
                                    <th class="fontBold">
                                        <span class="full-text"><?php echo localize("bo_leaderboard_table_all_new_direct"); ?></span>
                                        <span class="short-text"><?php echo localize("bo_leaderboard_table_all_new_direct_short"); ?></span>
                                    </th>
                                    <th class="fontBold">
                                        <span class="full-text"><?php echo localize("bo_leaderboard_table_all_new_total"); ?></span>
                                        <span class="short-text"><?php echo localize("bo_leaderboard_table_all_new_total_short"); ?></span>
                                    </th>
                                    <th class="fontBold">
                                        <?php echo localize("bo_leaderboard_table_all_access_volume"); ?>
                                    </th>
                                </thead>
                                <tbody id="tableAllBody" class="bo_text_table_body">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div style="text-align:center; margin: auto;">
                        <div id="divLoadMore" class="textLoadMore">
                            <?php echo localize("bo_leaderboard_table_all_load_more"); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div id="loadingMarqueeAll" class="loadingOverlayRelative">
                            <img src="./Images/loading.gif" class="loadingGif">
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>


    <script>
        var rankLastVisible = 0;
        var numPositionsPerLoad = 10;

        window.onload = function() {
            //document.getElementById("nav_downline").classList.add("active");
        }

        $(document).ready(function() {
            $.post("ajax_get_leaderboard_own_position.php", {}, function(data) {
                $("#loadingMarqueeYour").hide();
                try {
                    var response = JSON.parse(data);
                    if (response.code == 200) {
                        fillTableOwn(response.ownPosition);
                    }

                } catch (error) {
                    console.log(error);
                }
            });

            loadNextPartnersOfLeaderboard();

            $('#divLoadMore').click(function() {
                loadNextPartnersOfLeaderboard();
            });
        });

        function fillTableOwn(positions) {
            var tableBody = $("#tableOwnBody");
            tableBody.empty();

            for (const [position, partner] of Object.entries(positions)) {
                var tableRow = $("<tr></tr>");

                tableRow.append("<td>" + position + "</td>");

                var img = $("<img></img>");
                img.addClass("rounded-circle");
                img.addClass("bo_leaderboard_image");
                img.attr('src', partner.imageBase64);

                var name = partner["given_name"];
                if (partner["sur_name_0"])
                    name = name + " " + partner["sur_name_0"] + ".";
                name = name.trim();

                var cellName = $("<td></td>");
                cellName.append(img);
                cellName.append(" " + name);
                tableRow.append(cellName);

                var nameAffiliateLevel = partner.level_name;
                var cellRank = $("<td>" + nameAffiliateLevel + "</td>");
                cellRank.addClass("beige");
                tableRow.append(cellRank);

                tableBody.append(tableRow);
            }
        }

        function loadNextPartnersOfLeaderboard() {
            $('#divLoadMore').hide();
            $("#loadingMarqueeAll").show();
            $.post("ajax_get_leaderboard.php", {
                rankStart: rankLastVisible + 1,
                rankEnd: rankLastVisible + numPositionsPerLoad
            }, function(data) {
                $("#loadingMarqueeAll").hide();
                try {
                    var response = JSON.parse(data);
                    if (response.code == 200) {
                        fillTableLeaderboard(response.leaderboard);
                    }

                } catch (error) {
                    console.log(error);
                }
            });
        }

        function fillTableLeaderboard(positions) {
            var tableBody = $("#tableAllBody");
            // tableBody.empty();

            if (positions.length == 0)
                $('#divLoadMore').hide();
            else
                $('#divLoadMore').show();

            for (const [position, partner] of Object.entries(positions)) {
                rankLastVisible = partner.rank;

                var tableRow = $("<tr></tr>");

                tableRow.attr("data-user-id", partner.id);

                tableRow.append("<td>" + partner.rank + "</td>");

                var img = $("<img></img>");
                img.addClass("rounded-circle");
                img.addClass("bo_leaderboard_image");
                img.attr('src', partner.imageBase64);

                var name = partner["given_name"];
                if (partner["sur_name_0"])
                    name = name + " " + partner["sur_name_0"] + ".";
                name = name.trim();

                var cellName = $("<td></td>");
                cellName.append(img);
                cellName.append(" " + name);
                tableRow.append(cellName);

                var nameAffiliateLevel = partner.level_name
                var cellRank = $("<td>" + nameAffiliateLevel + "</td>");
                cellRank.addClass("beige");
                tableRow.append(cellRank);

                tableRow.append("<td>" + partner.downline_direct_count + "</td>");

                tableRow.append("<td>" + partner.downline_total_count + "</td>");

                tableRow.append("<td>" + partner.access_downline_total + "</td>");

                tableBody.append(tableRow);
            }
        }

    </script>

</body>

</html>