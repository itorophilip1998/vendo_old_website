<?php

include_once("translate.php");
include_once("configuration.php");
include_once("utils.php");
include_once("enums.php");

$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

include 'bo_read_user_logged_in.php';
// is logged in

if (!$user['is_admin']) {
    //silent fail 
    header("Location:bo_main.php");
}

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

            <div class="admin_dashboard_container_data">
                <div class="bo_panel">
                    <div class="row bo_panel_title flex-fill">
                        <div class="col">
                            <?php echo localize("admin_dashboard_title"); ?>
                        </div>
                    </div>

                    <div class="verticalSpacerS"></div>

                    <div class="row flex-fill">
                        <div class="col">
                            <div class="bo_text_normal info_label"><?php echo localize("admin_dashboard_sum_access_volume_paid"); ?></div>
                            <div id="divSumAccessVolumePaid" class="bo_text_big">&nbsp;</div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="admin_dashboard_container_users">
                <div class="bo_panel" style="min-height: 400px;">

                    <div class="row bo_panel_title">
                        <div class="col">
                            <?php echo localize("admin_dashboard_users_title"); ?>
                        </div>
                    </div>

                    <div class="verticalSpacer"></div>

                    <div class="row">
                        <div class="col">
                            <input id="inputSearchUser" type="text" class="profile_input input_text"></input>
                        </div>
                    </div>

                    <div id="divErrorSearchStringTooShort" class="hidden">
                        <div class="verticalSpacerS"></div>
                        <div class="warning-text"><?php echo localize("admin_dashboard_users_search_string_too_short"); ?></div>
                    </div>

                    <div id="divTextNothingFound" class="hidden">
                        <div class="verticalSpacerS"></div>
                        <div class="warning-text"><?php echo localize("admin_dashboard_users_search_nothing_found"); ?></div>
                    </div>

                    <div class="row bo_dl_table_members">
                        <div class="col">
                            <table id="tableUsers" class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
                                <thead class="bo_text_header table_bottom_line">
                                    <th class="fontBold">
                                        <?php echo localize("admin_dashboard_users_name"); ?>
                                    </th>
                                    <th class="fontBold">
                                        <?php echo localize("admin_dashboard_users_email"); ?>
                                    </th>
                                    <th class="fontBold" title="<?php echo localize("admin_dashboard_users_affiliate_level_tooltip"); ?>">
                                        <?php echo localize("admin_dashboard_users_affiliate_level"); ?>
                                    </th>
                                    <th class="fontBold">
                                        <?php echo localize("admin_dashboard_users_upline"); ?>
                                    </th>
                                    <th class="fontBold" title="<?php echo localize("admin_dashboard_users_trading_account_tooltip"); ?>">
                                        <?php echo localize("admin_dashboard_users_trading_account"); ?>
                                    </th>
                                    <th class="fontBold">
                                        <?php echo localize("admin_dashboard_users_automation"); ?>
                                    </th>
                                    <th class="fontBold">
                                        <?php echo localize("admin_dashboard_users_vqs"); ?>
                                    </th>
                                    <th class="fontBold" title="<?php echo localize("admin_dashboard_users_career_level_tooltip"); ?>">
                                        <?php echo localize("admin_dashboard_users_career_level"); ?>
                                    </th>
                                    <th class="fontBold" title="<?php echo localize("admin_dashboard_users_downline_level_tooltip"); ?>">
                                        <?php echo localize("admin_dashboard_users_downline_level"); ?>
                                    </th>
                                </thead>
                                <tbody id="tableUsersBody" class="bo_text_table_body">

                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="row">
                        <div id="loadingMarqueeSearchUsers" class="loadingOverlayRelative hidden">
                            <img src="./Images/loading.gif" class="loadingGif">
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>


    <script>
        var typingTimerSearchUser;
        var doneTypingIntervalSearchUser = 200; // time in ms
        var inputSearchUser = $('#inputSearchUser');

        var userId = -1;
        var amountVQs = 0;

        window.onload = function() {
            document.getElementById("nav_admin").classList.add("active");
        }

        $(document).ready(function() {
            loadUsers();
        });

        inputSearchUser.on('keyup', function() {
            clearTimeout(typingTimerSearchUser);
            typingTimerSearchUser = setTimeout(loadUsers, doneTypingIntervalSearchUser);
        });

        function loadUsers() {
            var searchString = inputSearchUser.val().trim();

            $("#divErrorSearchStringTooShort").addClass("hidden");
            $("#divTextNothingFound").addClass("hidden");
            $("#tableUsers").addClass("hidden");
            $("#tableUsersBody").empty();

            /*
            if(searchString === "")
                return;                
            */

            /*
			if(searchString.length<3) {
                $("#divErrorSearchStringTooShort").removeClass("hidden");
                return;
            }
            */

            $("#loadingMarqueeSearchUsers").removeClass("hidden");
            $("#divSumAccessVolumePaid").html("&nbsp;");

            $.post("ajax_admin_dashboard.php", {
                searchString: searchString
            }, function(data) {
                $("#loadingMarqueeSearchUsers").addClass("hidden")
                try {
                    var response = JSON.parse(data);
                    if (response.code == 200) {
                        $("#loadingMarquee").hide();
                        //$("#tableDownlineBody").html(response.commissions);

                        $("#divSumAccessVolumePaid").html("&nbsp;" + response.sumAccessVolumePaid);

                        if (response.users.length == 0) {
                            $("#divTextNothingFound").removeClass("hidden");
                        } else {
                            $("#tableUsers").removeClass("hidden");
                            fillTableUsers(response.users)
                        }
                    } else {
                        alert(response.message)
                    }

                } catch (error) {
                    console.log(error);
                }

            });
        }

        function fillTableUsers(users) {
            var tableBody = $("#tableUsersBody");

            tableBody.empty();
            for (let i = 0; i < users.length; i++) {
                var tableRow = $("<tr></tr>");
                tableRow.attr("data-user-id", users[i]["id"]);
                tableRow.attr("data-user-name", name);
                
                var name = users[i]["given_name"] + " " + users[i]["sur_name"];
                name = name.trim();
                tableRow.append("<td>" + name + "</td>");

                tableRow.append("<td>" + users[i]["email"] + "</td>");

                var cellAffiliateLevel = $("<td>" + users[i]["affiliate_level_name"] + "</td>");
                cellAffiliateLevel.addClass("beige");
                tableRow.append(cellAffiliateLevel);

                var nameUpline = users[i]["upline_given_name"] + " " + users[i]["upline_sur_name"];
                if (!users[i]["upline_given_name"] && !users[i]["upline_sur_name"])
                    nameUpline = "";
                nameUpline = nameUpline.trim();
                tableRow.append("<td>" + nameUpline + "</td>");

                tableRow.append("<td class=\"beige\">" + users[i]["trading_account_name"] + "</td>");

                if(!users[i]["automation"])
                    tableRow.append("<td></td>");
                else if(users[i]["automation"] == "Waiting_for_Active")
                    tableRow.append("<td>Wait1</td>");
                else if(users[i]["automation"] == "Waiting_for_Inactive")
                    tableRow.append("<td>Wait0</td>");
                else
                    tableRow.append("<td>" + users[i]["automation"] + "</td>");

                tableRow.append("<td>" + users[i]["vq_balance"] + "</td>");

                tableRow.append("<td>" + users[i]["career_level_name"] + "</td>");

                tableRow.append("<td>" + users[i]["downline_level"] + "</td>");

                //tableRow.click(tableClicked);
                tableBody.append(tableRow);
            }
        }
    </script>

</body>

</html>