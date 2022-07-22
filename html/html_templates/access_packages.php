<?php 
	include("translate.php");
	include("configuration.php");
?>
<div class="panel_logo_logo_input">
					<img src='Images/logo1.png' width=180 height=60></img>
				</div>

				<div class="panel_reg_fill_top">
				</div>

				<div class="regTradingAccountTitle">
					<?php echo localize("reg5_trading_account"); ?>
				</div>

				<div class="verticalSpacer"></div>

				<div class="green">
					<?php 
						$text = localize("reg5_trading_account_text_fee");
						$text = str_replace("[[RegistrationFee]]", REGISTRATION_FEE, $text);
						echo $text;
					?>
				</div>

				<div class="verticalSpacerS"></div>

				<table class="regTableTradingAccount">
					<tbody>
						<tr class="regTableTradingAccountHeader">
							<td class="regTableTradingAccountTdCol0 textSub regTableTradingAccountTd regTableTradingAccountTrBorder">
								<?php echo localize("reg5_trading_account_capital"); ?>
							</td>
							<td class="regTableTradingAccountTdCol1 textSub regTableTradingAccountTd regTableTradingAccountTrBorder">
								<?php echo localize("reg5_trading_account_symbol"); ?>
							</td>
							<td class="regTableTradingAccountTdCol2 textSub regTableTradingAccountTd regTableTradingAccountTrBorder">
								<?php echo localize("reg5_trading_account_fee"); ?>
							</td>
							<td>

							</td>
						</tr>
						<tr class="regTableTradingAccountRow acc_type basic" data-account="1">
							<td class="regTableTradingAccountTextCapital regTableTradingAccountTd">
								$ <?php echo number_format(ACCOUNT_1_AMOUNT, 0, '.', ','); ?>
							</td>
							<td class="regTableTradingAccountTd">
								<div class="regTableTradingAccountSymbolContainer">
									<div class="regTableTradingAccountSymbol floatLeft"><?php echo strtoupper(ACCOUNT_1_NAME) ?></div>
									<div id="regTableInfo1" class="regTableTradingAccountInfo showDetails">i</div>
								</div>
							</td>
							<td class="regTableTradingAccountTextFee regTableTradingAccountTd">
								$ <?php echo number_format(ACCOUNT_1_FEE, 2, '.', ','); ?>
							</td>
							<td class="regTableTradingAccountTd">
								<img src='Images/gift.svg' width=30 height=30></img>
							</td>
						</tr>
						<tr class="regTableTradingAccountRow acc_type plus" data-account="2">
							<td class="regTableTradingAccountTextCapital regTableTradingAccountTd">
								$ <?php echo number_format(ACCOUNT_2_AMOUNT, 0, '.', ','); ?>
							</td>
							<td class="regTableTradingAccountTd">
								<div class="regTableTradingAccountSymbolContainer">
									<div class="regTableTradingAccountSymbol floatLeft"><?php echo strtoupper(ACCOUNT_2_NAME) ?></div>
									<div id="regTableInfo2" class="regTableTradingAccountInfo showDetails">i</div>
								</div>
							</td>
							<td class="regTableTradingAccountTextFee regTableTradingAccountTd">
								$ <?php echo number_format(ACCOUNT_2_FEE, 2, '.', ','); ?>
							</td>
							<td class="regTableTradingAccountTd">
								<img src='Images/gift.svg' width=30 height=30></img>
							</td>
						</tr>
						<tr class="regTableTradingAccountRow acc_type pro" data-account="3">
							<td class="regTableTradingAccountTextCapital regTableTradingAccountTd">
								$ <?php echo number_format(ACCOUNT_3_AMOUNT, 0, '.', ','); ?>
							</td>
							<td class="regTableTradingAccountTd">
								<div class="regTableTradingAccountSymbolContainer">
									<div class="regTableTradingAccountSymbol floatLeft"><?php echo strtoupper(ACCOUNT_3_NAME) ?></div>
									<div id="regTableInfo3" class="regTableTradingAccountInfo showDetails">i</div>
								</div>
							</td>
							<td class="regTableTradingAccountTextFee regTableTradingAccountTd">
								$ <?php echo number_format(ACCOUNT_3_FEE, 2, '.', ','); ?>
							</td>
							<td class="regTableTradingAccountTd">
								<img src='Images/gift.svg' width=30 height=30></img>
							</td>
						</tr>
						<tr class="regTableTradingAccountRow acc_type pro_plus" data-account="4">
							<td class="regTableTradingAccountTextCapital regTableTradingAccountTd">
								$ <?php echo number_format(ACCOUNT_4_AMOUNT, 0, '.', ','); ?>
							</td>
							<td class="regTableTradingAccountTd">
								<div class="regTableTradingAccountSymbolContainer">
									<div class="regTableTradingAccountSymbol floatLeft"><?php echo strtoupper(ACCOUNT_4_NAME) ?></div>
									<div id="regTableInfo4" class="regTableTradingAccountInfo showDetails">i</div>
								</div>
							</td>
							<td class="regTableTradingAccountTextFee regTableTradingAccountTd">
								$ <?php echo number_format(ACCOUNT_4_FEE, 2, '.', ','); ?>
							</td>
							<td class="regTableTradingAccountTd">
								<img src='Images/gift.svg' width=30 height=30></img>
							</td>
						</tr>
					</tbody>
				</table>

				<div id="divErrorNoSelection" class="hidden">
					<div class="verticalSpacer"></div>
					<div class="warning-text"><?php echo localize("reg5_trading_account_error_missing"); ?></div>
				</div> 

				<div class="verticalSpacer"></div>

				<div class="textSub">
					<?php echo localize("reg5_trading_account_warning"); ?>
				</div>