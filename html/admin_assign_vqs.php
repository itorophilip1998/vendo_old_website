<?php

include_once("translate.php");
include_once("configuration.php");
include_once("utils.php");
include_once("enums.php");


$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

include 'bo_read_user_logged_in.php';
// is logged in

if (!$user['is_admin'])
{
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

            <div class="admin_assign_vqs_container_search">
                <div class="bo_panel ">
                    <div class="row bo_panel_title">
                        <div class="col">
                            <?php echo localize("bo_assign_vqs_title"); ?>
                        </div>
                    </div>

                    <div class="verticalSpacerS"></div>

                    <div class="row">
                        <div class="col">
                            <input id="inputSearchUser" type="text" class="profile_input input_text"></input>
                        </div>
                    </div>

                    <div id="divErrorSearchStringTooShort" class="hidden">
                        <div class="verticalSpacerS"></div>
                        <div class="warning-text" ><?php echo localize("bo_assign_vqs_search_string_too_short"); ?></div>
                    </div>

                    <div id="divTextNothingFound" class="hidden">
                        <div class="verticalSpacerS"></div>
                        <div class="warning-text" ><?php echo localize("bo_assign_vqs_search_nothing_found"); ?></div>
                    </div>

                </div>
            </div>

            <div id="panelResultList" class="admin_assign_vqs_container_list">
                <div class="bo_panel" style="min-height: 400px;">
                    <div class="row bo_panel_title">
                        <div class="col">
                            <?php echo localize("bo_assign_vqs_list_title"); ?>
                        </div>
                    </div>

					<div class="row bo_dl_table_members">
						<div class="col">
							<table id="tableUsers" class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
								<thead class="bo_text_header table_bottom_line">
									<th class="fontBold">
										<?php echo localize("bo_assign_vqs_table_user_name"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_assign_vqs_table_user_email"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_assign_vqs_table_user_depth"); ?>
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

    <div id="divModalInputVqs" class="modal_ta">
        <div class="modal_ta_content_2">
            <div id="div_modal_close" class="modal_ta_close_2">
				&times;
				<!-- span class="modal_ta_close_span">&times;</span -->
            </div>
                    
            <div class="bo_panel_title">
                <?php echo localize("bo_assign_vqs_modal_title"); ?>
            </div>
            
            <div class="verticalSpacerS"></div>

            <div>
                <input id="inputVQs" type="number" min="1" step="1" class="profile_input input_text">
                </input>
            </div>

            <div id="divErrorNoVQs" class="hidden">
                <div class="verticalSpacerS"></div>
                <div class="warning-text" ><?php echo localize("bo_assign_vqs_modal_no_vqs"); ?></div>
            </div>

            <div class="verticalSpacer"></div>

            <div>
                <input id="buttonAssignVQs" type="button" class="button_filled" value="<?php echo localize("bo_assign_vqs_modal_button_assign"); ?>"/>
            </div>
        </div>
    </div>

    <div id="divModalAffirmAssignment" class="modal_ta">
        <div class="modal_ta_content_2">
            <div id="div_modal_close_2" class="modal_ta_close_2">
				&times;
				<!-- span class="modal_ta_close_span">&times;</span -->
            </div>
                    
            <div id="divAffirmAssignmentText" class="bo_panel_title">                
            </div>

            <div class="verticalSpacer"></div>

            <div>
                <input id="buttonAffirmYes" type="button" class="button_filled" value="<?php echo localize("general_yes"); ?>"/>
                <input id="buttonAffirmNo" type="button" class="button_border" value="<?php echo localize("general_no"); ?>"/>
            </div>

            <div class="row">
                <div id="loadingMarqueeAssignVQs" class="loadingOverlayRelative hidden">
                    <img src="./Images/loading.gif" class="loadingGif">
                </div>
            </div>
        </div>
    </div>

    <div id="divModalFeedbackAssignment" class="modal_ta">
        <div class="modal_ta_content_2">
            <div id="div_modal_close_3" class="modal_ta_close_2">
				&times;
				<!-- span class="modal_ta_close_span">&times;</span -->
            </div>
                    
            <div id="divFeedbackAssignmentText" class="bo_panel_title">                
            </div>

            <div class="verticalSpacer"></div>

            <div>
                <input id="buttonFeedbackOk" type="button" class="button_filled" value="<?php echo localize("general_ok"); ?>"/>
            </div>

            <div class="row">
                <div id="loadingMarqueeAssignVQs" class="loadingOverlayRelative hidden">
                    <img src="./Images/loading.gif" class="loadingGif">
                </div>
            </div>
        </div>
    </div>

    <script>
        var typingTimerSearchUser;
        var doneTypingIntervalSearchUser = 200;	// time in ms
        var inputSearchUser = $('#inputSearchUser');

        var userId = -1;
        var amountVQs = 0;

        window.onload = function() {
            document.getElementById("nav_admin").classList.add("active");

            $("#div_modal_close").click(hideModals);
            $("#div_modal_close_2").click(hideModals);
            $("#div_modal_close_3").click(resetSearchAndhideModals);
            $("#buttonAssignVQs").click(assignClicked);
            $("#buttonAffirmNo").click(hideModals);
            $("#buttonAffirmYes").click(assignVQs);
            $("#buttonFeedbackOk").click(resetSearchAndhideModals);
        }

        $(document).ready(function() {
            loadUsers();
        });

        inputSearchUser.on('keyup', function () {
			clearTimeout(typingTimerSearchUser);
			typingTimerSearchUser = setTimeout(loadUsers, doneTypingIntervalSearchUser);
		});

        $('#inputVQs').on('keydown', function (event) {
            if((event.key < '0' || event.key > '9') && event.keyCode != 8 && event.keyCode != 27 && event.keyCode != 35 && event.keyCode != 36 && event.keyCode != 37 && event.keyCode != 39 && event.keyCode != 46 && event.keyCode != 123 && event.keyCode != 116 ) {
                event.preventDefault();
            }
        });
        
        function loadUsers() {
			var searchString=inputSearchUser.val().trim();

			$("#divErrorSearchStringTooShort").addClass("hidden");
			$("#divTextNothingFound").addClass("hidden");
            $("#panelResultList").addClass("hidden");
            $("#tableUsersBody").empty();

            if(searchString === "")
                return;                

			if(searchString.length<3) {
                $("#divErrorSearchStringTooShort").removeClass("hidden");
                return;
            }
            
            $("#loadingMarqueeSearchUsers").removeClass("hidden");

            $.post("ajax_search_users.php",{searchString: searchString}, function(data) {
                $("#loadingMarqueeSearchUsers").addClass("hidden")
                try {
                    var response = JSON.parse(data);
                    if(response.code == 200) {
                        $("#loadingMarquee").hide();
                        //$("#tableDownlineBody").html(response.commissions);

                        if(response.users.length == 0) {
                            $("#divTextNothingFound").removeClass("hidden");
                        } else {
                            $("#panelResultList").removeClass("hidden");
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
			var tableBody=$("#tableUsersBody");
						
			tableBody.empty();
			for(let i = 0; i < users.length; i++) {
				var tableRow = $("<tr></tr>");
				var name=users[i]["given_name"]+ " " + users[i]["sur_name"];
				name=name.trim();				
                tableRow.attr("data-user-id", users[i]["id"]);
                tableRow.attr("data-user-name", name);
				tableRow.append("<td>"+name+"</td>");
				tableRow.append("<td>"+users[i]["email"]+"</td>");
                tableRow.append("<td>"+users[i]["downline_level"]+"</td>");
				tableRow.click(tableClicked);
				tableBody.append(tableRow);
			}
        }
        
        function tableClicked(event) {
            window.scrollTo(0,0);
            $("#divToBlur").addClass("blur");
            $("#divModalInputVqs").show();
            $("#inputVQs").val("");
            $("#divErrorNoVQs").addClass("hidden");
            userId=$(event.target).parent().attr("data-user-id");
            userName=$(event.target).parent().attr("data-user-name");
        }

        function assignClicked(event) {
            amountVQs = $("#inputVQs").val();
            if(amountVQs == 0) {
                $("#divErrorNoVQs").removeClass("hidden");
            } else {
                $("#divModalInputVqs").hide();
                var textAffirm = "<?php echo localize("bo_assign_vqs_modal_affirm_text"); ?>";
                textAffirm = textAffirm.replace("[[userName]]", userName)
                textAffirm = textAffirm.replace("[[amountVQs]]", amountVQs)
                $("#divAffirmAssignmentText").html(textAffirm);
                $("#divModalAffirmAssignment").show();
                //assignVQs(userId, amountVQs);
            }
        }

        function assignVQs(event) {
            $("#loadingMarqueeAssignVQs").removeClass("hidden")

            $.post("ajax_assign_vqs.php",{userId: userId, vqs: amountVQs}, function(data) {
                $("#loadingMarqueeAssignVQs").addClass("hidden")
                try {
                    var response = JSON.parse(data);
                    console.log(response);
                    if(response.code == 200) {
                        showFeedback();
                    } else {
                        alert(response.message)
                    }

                } catch (error) {
                    alert(error);
                    console.log(error);
                }
            
            });
        }

        function showFeedback() {
            var textFeedback = "<?php echo localize("bo_assign_vqs_modal_feedback_text"); ?>";
            textFeedback = textFeedback.replace("[[userName]]", userName)
            textFeedback = textFeedback.replace("[[amountVQs]]", amountVQs)
            $("#divFeedbackAssignmentText").html(textFeedback);
            $("#divModalFeedbackAssignment").show();

        }

        function hideModals() {
            $("#divToBlur").removeClass("blur");
            $("#divModalInputVqs").hide();
            $("#divModalAffirmAssignment").hide();
            $("#divModalFeedbackAssignment").hide();
        }

        function resetSearchAndhideModals() {
            inputSearchUser.val("")
            loadUsers();
            hideModals();
        }

    </script>

</body>

</html>