<?php
include_once("translate.php");
include_once("configuration.php");
include_once("utils.php");
include_once("enums.php");
require_once('lib/commissions.php');

require_once("./lib/protoncapitalmarkets.php");

use Brokers\ProtonCapitalMarketsBroker;
use Brokers\ProtonCapitalMarketsException;

try {

	$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	include 'bo_read_user_logged_in.php';
	// is logged in

	try {

		$sponsorTree_ = loadSponsorTree($pdo, $user["id"], false, 1);
	} catch (Exception $e) {
		$msg = "exception: " . $e->getMessage();
		error_log($msg);
	}

	//if login token not generated on login - try to generate it now
	try {
		$broker = new ProtonCapitalMarketsBroker(PROTONCAPITALMARKETS_SERVERNAME, PROTONCAPITALMARKETS_AUTHCODE);
		if ((!isset($_SESSION["loginTokenProtonCapitalMarkets"]) || empty($_SESSION["loginTokenProtonCapitalMarkets"])) && (!empty($user['md5_hash']))) {
			$_SESSION["loginTokenProtonCapitalMarkets"] = $broker->generateLoginToken($user['email'], $user['md5_hash'], $user['id']);
		}
	} catch (ProtonCapitalMarketsException $e) {
		//ignore, the failure is handled in next if block
		error_log($e->getMessage());
	} catch (Exception $e) {
		error_log($e->getMessage());
	}

	$broker_registration_complete = $user['broker_registration_complete'];
	$trading_account_type = getNameTradingAccount($user['trading_account']);
	$broker_login_successful = false;
	$broker_link_target = "_blank";

	if (isset($_SESSION["loginTokenProtonCapitalMarkets"]) && !empty($_SESSION["loginTokenProtonCapitalMarkets"])) {
		$broker_login_successful = true;
		$broker_autologin_link = "https://www.protoncapitalmarkets.com/loginwl.php?token=" . $_SESSION["loginTokenProtonCapitalMarkets"];

		//if successfull - clear the unsecure md5 hash
		if (!empty($user['md5_hash'])) {
			userClearMd5Hash($user['id']);
		}
	} else {
		if (!$broker_registration_complete) {
			$broker_login_successful = false;
			require_once('bitcoin.php');
			$pending_payment = BitCoin::getPendingPayment($user['id']);

			if ($pending_payment['status'] == 'pending') {
				$broker_autologin_link = $pending_payment['link'];
				$trading_account_type = localize('bo_main_pending_payment');
			} else if ($pending_payment['status'] == 'timeout' || $pending_payment['status'] == 'expired') {
				$broker_autologin_link = $pending_payment['link'];
				$trading_account_type = localize('bo_main_payment_timeout');
				$broker_link_target = "";
			} else {
				//no open payment - maybe some problem with proton registration
				$broker_link_target = "_blank";
				$broker_autologin_link = "mailto:office@vendo.club";
				$trading_account_type = localize('bo_main_broker_error');
			}


			$trading_account_add_class = "red";
		} else //there must be some problem with broker login
		{
			$broker_autologin_link = "https://www.protoncapitalmarkets.com/login.php";
			$broker_warning = " (" . localize('bo_main_broker_login_failed') . ")";
		}
	}

	$waitingForChangeAutomation = false;
	$waitingForChangeAutomationText = "";
	if ($user['automation'] != AutomationType::OFF && $user['automation'] != AutomationType::ON) {
		$InfoUser = [];
		try {
			if ($broker_login_successful) {
				$InfoUser = $broker->getInfo($user['id']);
			}

			if (AutomationChanged($InfoUser['AutoTradeStatus'], $user['automation'])) {
				updateUser($pdo, $user['id'], [
					'automation' => $InfoUser['AutoTradeStatus'],
					'AccountNumber' => $InfoUser['AccountNumber']
				]);
			} else {
				if ($user['automation'] == AutomationType::WAITING_FOR_INACTIVE) {
					$waitingForChangeAutomation = true;
					$waitingForChangeAutomationText = localize('bo_main_automation_change_pending_off', $user['language']);
				} else if ($user['automation'] == AutomationType::WAITING_FOR_ACTIVE) {
					$waitingForChangeAutomation = true;
					$waitingForChangeAutomationText = localize('bo_main_automation_change_pending_on', $user['language']);
				}

				$InfoUser['AutoTradeStatus'] = $user['automation'];
				$InfoUser['AccountNumber'] = $user['AccountNumber'];
			}
		} catch (Exception $e) {
		}
	} else {
		$InfoUser['AutoTradeStatus'] = $user['automation'];
		$InfoUser['AccountNumber'] = $user['AccountNumber'];
	}

	$classesAutomation = "automatic_green";
	$automationType = localize('bo_automation_on', $user['language']);

	$access_downline_total = $user['access_downline_total'];
	$balance = $user['balance'];

	$addClassesOn = "hidden";
	$addClassesOff = "";

	if ($InfoUser['AutoTradeStatus'] == "Off") {
		$addClassesOff = "hidden";
		$addClassesOn = "";

		$classesAutomation = "automatic_red";
		$automationType = localize('bo_automation_off', $user['language']);
	}

	$automation_qualification = ($balance >= 250);

	$date = date("d.m.Y H:i:s");

	$emailTexton = localize('bo_change_automatic_email_text', $user['language']);
	$emailTexton = str_replace(":Accounttype", $trading_account_type, $emailTexton);
	$emailTexton = str_replace(":CurrentStatus", "START", $emailTexton);
	$emailTexton = str_replace(":CurrentDate", $date, $emailTexton);
	$emailTexton = str_replace(":Accountnumber", $InfoUser['AccountNumber'], $emailTexton);
	$emailTexton = str_replace(":Fullname", $user['given_name'] . " " . $user['sur_name'], $emailTexton);
	$emailTexton = str_replace(":Email", $user['email'], $emailTexton);
	$emailTexton = str_replace(":state", localize('bo_change_automatic_activate', $user['language']), $emailTexton);
	$emailTexton = str_replace(":oldStatus", localize('bo_change_automatic_activation', $user['language']), $emailTexton);
	$emailTexton = str_replace(":FeeText", localize('bo_change_automatic_fee_text', $user['language']), $emailTexton);
	$emailTexton = trim($emailTexton);

	$emailTextoff = localize('bo_change_automatic_email_text', $user['language']);
	$emailTextoff = str_replace(":Accounttype", $trading_account_type, $emailTextoff);
	$emailTextoff = str_replace(":CurrentStatus", "STOP", $emailTextoff);
	$emailTextoff = str_replace(":CurrentDate", $date, $emailTextoff);
	$emailTextoff = str_replace(":Accountnumber", $InfoUser['AccountNumber'], $emailTextoff);
	$emailTextoff = str_replace(":Fullname", $user['given_name'] . " " . $user['sur_name'], $emailTextoff);
	$emailTextoff = str_replace(":Email", $user['email'], $emailTextoff);
	$emailTextoff = str_replace(":state", localize('bo_change_automatic_deactivate', $user['language']), $emailTextoff);
	$emailTextoff = str_replace(":oldStatus", localize('bo_change_automatic_deactivation', $user['language']), $emailTextoff);
	$emailTextoff = trim($emailTextoff);

	$emailTextoff = str_replace(":FeeText", "", $emailTextoff);

	$PerformanceFee = getPerformanceFee($user['trading_account']);
	$emailTextoff = str_replace(":PerformanceFee", $PerformanceFee, $emailTextoff);
	$emailTexton = str_replace(":PerformanceFee", $PerformanceFee, $emailTexton);


	$acceptAGB = localize('bo_agb_must_be_confirmed', $user['language']);

	$emailSubjectOn = localize('bo_automatic_send_mail_subject', $user['language']);
	$emailSubjectOff = localize('bo_automatic_send_mail_subject_end', $user['language']);

	$confirmAGB = localize('bo_confirm_agb_send_mail', $user['language']);

	$confirmSend = localize('bo_confirm_send_mail', $user['language']);
	$declineSend = localize('bo_decline_send_mail', $user['language']);

	$warningText = localize('bo_automatic_turn_off_warning', $user['language']);

	$greetingText = localize('bo_main_greeting', $user['language']);
	$greetingText = str_replace(":Name", $user['given_name'], $greetingText);

	$copyText = localize('bo_main_copy_button', $user['language']);

	$profileContainerTitle = localize('bo_main_profile_container_title', $user['language']);
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


		<div class="bo_container_content main-color">
			<div class="bo_container_profile">
				<div class="bo_panel">
					<div class="row bo_panel_title mb-4">
						<div class="col">
							<?= $profileContainerTitle ?>
						</div>
						<div class="col text-right">
							<a class="bo_panel_title" href="./bo_profile.php">
								<svg class="bi bi-arrow-up-right" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" d="M6.5 4a.5.5 0 01.5-.5h5a.5.5 0 01.5.5v5a.5.5 0 01-1 0V4.5H7a.5.5 0 01-.5-.5z" clip-rule="evenodd"></path>
									<path fill-rule="evenodd" d="M12.354 3.646a.5.5 0 010 .708l-9 9a.5.5 0 01-.708-.708l9-9a.5.5 0 01.708 0z" clip-rule="evenodd"></path>
								</svg>
							</a>
						</div>
					</div>
					<div class="row flex-fill mb-3">
						<div class="col">
							<?php if ($user['profile_picture_name']) : ?>
								<img class="rounded-circle bo_profile_image" src="<?= PROFILE_PICTURE_ROOT_DIR . $user['id'] . PICTURE_DIR . $user['profile_picture_name'] ?>" alt="no valid profile image"></img>
							<?php else : ?>
								<img src="Images/profile.jpg" class="rounded-circle bo_profile_image">
							<?php endif; ?>
						</div>
					</div>
					<div class="row flex-fill mb-3">
						<div class="col bo_text_profile_greeting">
							<?= $greetingText ?>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="bo_text_normal">Access:</div>
							<div class="bo_text_normal beige inline bold"><a class="beige <?php echo $trading_account_add_class; ?>" href="<?php echo $broker_autologin_link; ?>" target="<?php echo $broker_link_target; ?>"><?php echo $trading_account_type; ?>
									<svg style="font-size: 17pt;" class="bi bi-arrow-up-right" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" d="M6.5 4a.5.5 0 01.5-.5h5a.5.5 0 01.5.5v5a.5.5 0 01-1 0V4.5H7a.5.5 0 01-.5-.5z" clip-rule="evenodd"></path>
										<path fill-rule="evenodd" d="M12.354 3.646a.5.5 0 010 .708l-9 9a.5.5 0 01-.708-.708l9-9a.5.5 0 01.708 0z" clip-rule="evenodd"></path>
									</svg>
								</a>
								<?php if (!empty($broker_warning)) : ?>
									<div class="bo_text_small red"><?php echo $broker_warning; ?></div>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<?php if ($broker_login_successful == true) : ?>
						<div class="row flex-fill mb-3">
							<div id="automationControl" class="col <?php if ($automation_qualification != true) echo "hidden"; ?>">
								<div class="bo_text_normal">Automatic:</div>
								<div id="automationCheckbox" class="inline">
									<?php if (!$waitingForChangeAutomation) : ?>
										<div class="onoffswitch">
											<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="automaticOnOff" <?php if ($InfoUser['AutoTradeStatus'] != "Off") { ?> checked <?php } ?> />
											<label class="onoffswitch-label" for="automaticOnOff" style="margin: 0.1px">
												<span class="onoffswitch-inner"></span>
												<span class="onoffswitch-switch"></span>
											</label>
										</div>
										<div id="text_automaticOnOff" class="bo_text_normal inline bold <?php echo $classesAutomation; ?>"><?php echo $automationType ?></div>
									<?php else : ?>
										<?= $waitingForChangeAutomationText ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<!-- <div class="row">
						<div class="col">
							<div class="bo_text_profile_stock_balance">$ 3.465</div>
						</div>
					</div>
					<hr>
					<div class="row secondary-color bo_text_small">
						<div class="col ml-2 mr-4">
							<div style="left: 0%; top: -23px; position: absolute">0k</div>
							<div style="left: 25%; top: -23px; position: absolute">1k</div>
							<div style="left: 50%; top: -23px; position: absolute">2k</div>
							<div style="left: 75%; top: -23px; position: absolute">3k</div>
							<div style="left: 100%; top: -23px; position: absolute">4k</div>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<div class="progress">
								<div class="progress-bar bg-warning" role="progressbar" style="width: 86.25%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
							</div>
						</div>
					</div>
					<div class="row secondary-color bo_text_small">
						<div class="col">
							You haven't reached your recommended volume yet. You're still missing 123123
						</div>
					</div> -->
				</div>
			</div>
			<div class="bo_container_members">
				<div class="bo_panel">
					<div class="row bo_panel_title flex-fill">
						<div class="col">
							<?php echo localize("bo_stats_members"); ?>
						</div>
						<div class="col text-right">
							<!-- div class="dropdown">
								<button class="btn btn-secondary dropdown-toggle dropdownBackground dropdownButton bo_text_normal" type="button" id="filterMembers" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									All time
								</button>
								<div class="dropdown-menu" aria-labelledby="filterMembers">
									<a class="dropdown-item" href="#">Action</a>
									<a class="dropdown-item" href="#">Another action</a>
									<a class="dropdown-item" href="#">Something else here</a>
								</div>
							</div -->
						</div>
					</div>

					<div class="row flex-fill">
						<div class="col">
							<div class="bo_text_normal info_label"><?php echo localize("bo_stats_direct_members"); ?></div>
							<div class="bo_text_big">&nbsp;<?php echo $sponsorTree_["data"]["downline_direct_count"] ?></div>
						</div>
					</div>
					<div class="bo_separator"></div>
					<div class="row flex-fill">
						<div class="col">
							<div class="bo_text_normal info_label"><?php echo localize("bo_stats_total_members"); ?></div>
							<div class="bo_text_big">&nbsp;<?php echo $sponsorTree_["data"]["downline_total_count"] ?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="bo_container_turnover">
				<div class="bo_panel">
					<div class="row bo_panel_title flex-fill">
						<div class="col">
							<?php echo localize('bo_main_turnover'); ?>
						</div>
						<!--
						<div class="col text-right">
							<div class="dropdown">
								<button class="btn btn-secondary dropdown-toggle dropdownBackground dropdownButton bo_text_normal" type="button" id="filterMembers" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									All time
								</button>
								<div class="dropdown-menu" aria-labelledby="filterMembers">
									<a class="dropdown-item" href="#">Action</a>
									<a class="dropdown-item" href="#">Another action</a>
									<a class="dropdown-item" href="#">Something else here</a>
								</div>
							</div>
						</div>
									-->
					</div>
					<div class="row flex-fill">
						<div class="col">
							<div class="bo_text_normal info_label"><?php echo localize('bo_main_access'); ?></div>
							<div class="bo_text_big">$&nbsp;<?php echo number_format($access_downline_total, 2, '.', ','); ?></div>
						</div>
					</div>
					<div class="bo_separator"></div>
					<div class="row flex-fill">
						<div class="col">
							<div class="bo_text_normal info_label"><?php echo localize('bo_main_volume'); ?></div>
							<div class="bo_text_big">$&nbsp;<span id="volume_value"><?php echo number_format($balance, 2, '.', ','); ?></span></div>
						</div>
					</div>
				</div>
			</div>
			<div class="bo_container_leaderboard">
				<div class="bo_panel">
					<div class="row bo_panel_title">
						<div class="col">
							<?php echo localize('bo_main_leaderboard_title'); ?>
						</div>
						<div class="col text-right">
							<a class="bo_panel_title" href="./bo_leaderboard.php">
								<svg class="bi bi-arrow-up-right" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" d="M6.5 4a.5.5 0 01.5-.5h5a.5.5 0 01.5.5v5a.5.5 0 01-1 0V4.5H7a.5.5 0 01-.5-.5z" clip-rule="evenodd"></path>
									<path fill-rule="evenodd" d="M12.354 3.646a.5.5 0 010 .708l-9 9a.5.5 0 01-.708-.708l9-9a.5.5 0 01.708 0z" clip-rule="evenodd"></path>
								</svg>
							</a>
						</div>
					</div>
					<div class="row">
						<div class="col">
							<table class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
								<thead class="secondary-color bo_text_header table_bottom_line">
									<tr>
										<th><?php echo localize('bo_main_leaderboard_table_pos'); ?></th>
										<th><?php echo localize('bo_main_leaderboard_table_partner'); ?></th>
										<th><?php echo localize('bo_main_leaderboard_table_rank'); ?></th>
									</tr>
								</thead>
								<tbody id="tableLeaderboardBody" class="bo_text_table_body">

								</tbody>
							</table>
						</div>
					</div>

					<div class="row">
                        <div id="loadingMarqueeLeaderboard" class="loadingOverlayRelative">
                            <img src="./Images/loading.gif" class="loadingGif">
                        </div>
                    </div>

				</div>
			</div>
			<div class="bo_container_recommendation_codes">
				<div class="bo_panel" style="min-height: 500px;">
					<div class="row">
						<div class="col">
							<div class="bo_panel_title secondary-color">
								<?php echo localize("bo_main_code_title"); ?>
							</div>
						</div>
					</div>

					<div class="row mt-4">
						<div class="col">
							<div class="bo_text_normal">
								<?php echo localize("bo_main_code_text"); ?>
							</div>
						</div>
					</div>
					<div class="verticalSpacer"></div>
					<div class="row">
						<div class="col">
							<input id="buttonGenerateCode" type="button" class="button_filled" value="<?php echo localize("bo_main_code_button"); ?>" />
							<input id="inputCode" type="text" class="bo_main_input_code" disabled value="" />
							<div id="CopyButton" class="beige inline bold copyButton pointer"><?= $copyText ?></div>
						</div>
					</div>

					<div class="row mt-4 bo_main_table_codes">
						<div class="col">
							<table class="table table-sm table-dark mt-3 table-borderless align-self-center" style="background: transparent;">
								<thead class="bo_text_header table_bottom_line">
									<th class="fontBold">
										<?php echo localize("bo_main_code_table_nr"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_main_code_table_code"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_main_code_table_date"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_main_code_table_valid"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_main_code_table_status"); ?>
									</th>
									<th class="fontBold">
										<?php echo localize("bo_main_code_table_member"); ?>
									</th>
								</thead>
								<tbody id="tableCodesBody" class="bo_text_table_body">
								</tbody>
							</table>
							<table class="hidden">
								<tbody>
									<tr id="tableRowCodesTemplate" class="hidden">
										<td class="bo_main_table_codes_td">
										</td>
										<td class="bo_main_table_codes_td">
										</td>
										<td class="bo_main_table_codes_td">
										</td>
										<td class="bo_main_table_codes_td">
										</td>
										<td class="bo_main_table_codes_td">
										</td>
										<td class="bo_main_table_codes_td">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- div class="container-all col-lg-9 col-md-9 col-sm-12 col-xs-12">
		<div class="container-article">

			<div>
				<h2>Temporary entry codes</h2>
			</div>

			<div class="hidden" id="div_codes_nothing">
				There are currently no valid codes.
			</div>

			<div class="table-responsive table_max_height hidden" id="table_codes_wrapper">
				<table class="tabledef">
					<thead>
						<tr>
							<th>Code</th>
							<th>Time</th>
						</tr>
					</thead>
					<tbody id="table_codes_body">
					</tbody>
				</table>
			</div>

			<div>
				<input id="buttonCreateCode" class="button-affirm" type="submit" name="submit" value="Create Code">
			</div>

		</div>
	</div -->

	<?php if ($broker_login_successful == true) : ?>

		<div id="modal_warning_automatic_trading" class="modal_ta">
			<!-- Modal content -->
			<div class="modal_ta_content">

				<div class="center_horizontally alert">
				</div>

				<div class="verticalSpacer"></div>

				<div id="divInfoText" class="text_modal center_text center_horizontally w80">
					<?php echo $warningText; ?>
					<br><br><br>
					<div style="display: inline-block;">
						<input id="buttonChangeAutomatic" type="button" class="button_filled button_transparent inline button_text_modal_orange button_modal_outline no_shadow" value="<?php echo localize("bo_turn_off"); ?>" />
						&nbsp;&nbsp;&nbsp;
						<input type="button" class="div_modal_warning_close button_filled inline button_text_modal_black" value="<?php echo localize("bo_keep_automatic", $user['language']); ?>" />
					</div>
				</div>

			</div>
		</div>

		<div id="modal_email_info" class="modal_email">
			<!-- Modal content -->
			<div class="modal_email_content">

				<textarea disabled id="subjectInfoTextOn" class="modal_email_text_normal <?= $addClassesOn ?>" style="height: 30px;"><?= $emailSubjectOn ?></textarea>
				<textarea disabled id="subjectInfoTextOff" class="modal_email_text_normal <?= $addClassesOff ?>" style="height: 30px;"><?= $emailSubjectOff ?></textarea>
				<textarea disabled id="divInfoTextOn" class="modal_email_text_normal <?= $addClassesOn ?>" style="height: 200px;"><?= $emailTexton ?></textarea>
				<textarea disabled id="divInfoTextOff" class="modal_email_text_normal <?= $addClassesOff ?>" style="height: 200px;"><?= $emailTextoff ?></textarea>
				<br>
				<input id="checkboxConfirmAGB" name="confirmAGB" type="checkbox"><span class="main-color" style="margin-left: 8px;"><?= $confirmAGB ?></span>
				<div id="notacceptAGB" class="red hidden"><?= $acceptAGB ?></div>
				<br><br>
				<div class="text-center">
					<input id="confirmSendMail" type="button" class="button_filled button_text_modal_black" value="<?= $confirmSend ?>" />
					<input type="button" class="div_modal_warning_close button_filled button_text_modal_black" value="<?= $declineSend ?>" />
				</div>

			</div>
		</div>
	<?php endif; ?>

	<!-- sidebar -->
	<!-- ?php
	include "bo_sidebar.php";
	? -->

	<script>
		window.onload = function() {

			document.getElementById("nav_main").classList.add("active");
			document.getElementById("buttonGenerateCode").addEventListener("click", generateCode);
			loadCodes();
			updateTradingInfo();
			updateTradingHistory();
			loadLeaderboard();
		}


		function generateCode() {
			$.ajax("ajax_create_temporary_code.php", {
					data: {
						userId: <?php echo $user["id"] ?>
					},
					dataType: "json"
				})
				.done(function(data) {
					if (data.code == 1) {
						document.getElementById("inputCode").value = data.temporaryCode;
						loadCodes();
					} else {
						console.log(data)
					}
				})
				.fail(function(xhr, status, error) {
					// console.log(xhr);
					// console.log(status);
					// console.log(error);
				})
				.always(function(xhr, status, error) {})
		}

		function updateTradingInfo() {
			$.ajax("ajax_update_trading_info.php", {
					dataType: "json"
				})
				.done(function(data) {
					if (data.code == 0) {
						var balance = parseFloat(data.info.TradeInfo.Balance);
						//show automation button
						if (balance >= 250) {
							$("#automationControl").removeClass('hidden');
						}
						//update volume
						if (balance >= 0) {
							$("#volume_value").text(numeral(balance).format('0,0.00'));
						}
					} else {
						console.log(data)
					}
				})
				.fail(function(xhr, status, error) {
					// console.log(xhr);
					// console.log(status);
					// console.log(error);
				})
				.always(function(xhr, status, error) {})
		}

		function updateTradingHistory() {
			$.ajax("ajax_update_trading_history.php", {
					dataType: "json"
				})
				.done(function(data) {
					//nothing todo (for now)
				})
				.fail(function(xhr, status, error) {
					// console.log(xhr);
					// console.log(status);
					// console.log(error);
				})
				.always(function(xhr, status, error) {})
		}

		function loadCodes() {
			//document.getElementById("div_codes_nothing").classList.add("hidden");
			//document.getElementById("table_codes_wrapper").classList.add("hidden");

			$.ajax("ajax_get_temporary_codes.php", {
					data: {
						userId: <?php echo $user["id"] ?>
					},
					dataType: "json"
				})
				.done(function(data) {
					if (data.code == 1) {
						fillTableCodes(data.temporaryCodes);
					} else {
						console.log(data)
					}
				})
				.fail(function(xhr, status, error) {
					// console.log(xhr);
					// console.log(status);
					// console.log(error);
				})
				.always(function(xhr, status, error) {})
		}

		function fillTableCodes(codesInfo) {
			var tableBody = $("#tableCodesBody");
			tableBody.empty();
			for (var i = 0; i < codesInfo.length; i++) {
				var tableRow = $("<tr></tr>");

				tableRow.append("<td>" + (codesInfo.length - i) + "</td>");
				tableRow.append("<td>" + codesInfo[i]["code"] + "</td>");

				var dateMoment = moment.utc(codesInfo[i]["date"], "YYYY-MM-DD HH:mm:ss");
				var dateText = dateMoment.local().format("DD.MM.YYYY");
				tableRow.append("<td>" + dateText + "</td>");

				var timeDiffS = dateMoment.diff(moment().utc()) / 1000;
				var timeDiffS = timeDiffS + <?php echo DURATION_TEMPORARY_CODE_VALID_SECONDS ?>;
				var timeDiffM = Math.floor(timeDiffS / 60);
				var timeDiffSS = Math.floor(timeDiffS % 60);
				var textTimeLeft = "00:00"
				var classColorTimeLeft = "grey";
				if (timeDiffS > 0) {
					textTimeLeft = (timeDiffM < 10 ? "0" : "") + timeDiffM + ":" + (timeDiffSS < 10 ? "0" : "") + timeDiffSS;
					classColorTimeLeft = "beige";
				}

				var textStatus = "<?php echo localize("bo_main_code_table_status_pending"); ?>";
				var classColorStatus = "beige";
				var textName = "-"
				if (parseInt(codesInfo[i]["user_id"]) >= 0) {
					textName = codesInfo[i]["given_name"] + " " + codesInfo[i]["sur_name"];
					textTimeLeft = "00:00";
					classColorTimeLeft = "grey";
					if (codesInfo[i]["broker_registration_complete"] == 1)
					{
						textStatus = "<?php echo localize("bo_main_code_table_status_member"); ?>";
						classColorStatus = "green";
					}
					else{
						textStatus = "<?php echo localize("bo_main_code_table_status_pending"); ?>";
						classColorStatus = "beige";
					}
				} else if (timeDiffS <= 0) {
					textStatus = "<?php echo localize("bo_main_code_table_status_expired"); ?>";
					classColorStatus = "red";
				}

				tableRow.append("<td class='" + classColorTimeLeft + "'>" + textTimeLeft + "</td>");
				tableRow.append("<td class='" + classColorStatus + "'>" + textStatus + "</td>");
				tableRow.append("<td>" + textName + "</td>");

				tableBody.append(tableRow);
			}
		}

		function loadLeaderboard() {
			$.post("ajax_get_leaderboard_own_position.php", {}, function(data) {
				$("#loadingMarqueeLeaderboard").hide();
                try {
                    var response = JSON.parse(data);
                    if (response.code == 200) {
                        fillTableLeaderboard(response.ownPosition);
                    }

                } catch (error) {
                    console.log(error);
                }
            });
		}

		function fillTableLeaderboard(positions) {
            var tableBody = $("#tableLeaderboardBody");
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

				var nameAffiliateLevel = partner.level_name
                var cellRank = $("<td>" + nameAffiliateLevel + "</td>");
                cellRank.addClass("beige");
				tableRow.append(cellRank);
				
				tableBody.append(tableRow);
            }
		}
		
	</script>

	<script src="./lib/js/bo_main.js"></script>
</body>

</html>