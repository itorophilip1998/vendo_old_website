<table class="regTableTradingAccount">
	<thead>
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
	</thead>
	<tbody>
		<tr class="regTableTradingAccountRow acc_type basic" data-account="1">
			<?php if($showUpToInfo < 1): ?>
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
					$ <?php echo number_format($amountAccount1, 2, '.', ','); ?>
				</td>
				<td class="regTableTradingAccountTd additionalSymbol">
					<img src='Images/gift.svg' width=30 height=30></img>
				</td>
			<?php endif; ?>
		</tr>
	
		<tr class="regTableTradingAccountRow acc_type plus" data-account="2">
			<?php if($showUpToInfo < 2): ?>
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
					$ <?php echo number_format($amountAccount2, 2, '.', ','); ?>
				</td>
				<td class="regTableTradingAccountTd additionalSymbol">
					<img src='Images/gift.svg' width=30 height=30></img>
				</td>
				<?php endif; ?>
			</tr>
		
		<tr class="regTableTradingAccountRow acc_type pro" data-account="3">
			<?php if($showUpToInfo < 3): ?>
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
					$ <?php echo number_format($amountAccount3, 2, '.', ','); ?>
				</td>
				<td class="regTableTradingAccountTd additionalSymbol">
					<img src='Images/gift.svg' width=30 height=30></img>
				</td>
				<?php endif; ?>
			</tr>


		<tr class="regTableTradingAccountRow acc_type pro_plus" data-account="4">
			<?php if($showUpToInfo < 4): ?>
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
					$ <?php echo number_format($amountAccount4, 2, '.', ','); ?>
				</td>
				<td class="regTableTradingAccountTd additionalSymbol">
					<img src='Images/gift.svg' width=30 height=30></img>
				</td>
				<?php endif; ?>
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


<script>
	function regTableInfoClicked(accountType) {
		$(".reg_slidein_info").removeClass("away");
	}

	function selectAccount(account) {
		$(".acc_type").removeClass("active");
		$(".acc_type."+account).addClass("active");

		document.getElementById("divErrorNoSelection").classList.add("hidden");
	}
	
	function getSelectedAccount() {
		var elem = $(".regTableTradingAccountRow.active");
		if (!elem.length)
		{
			return null;
		}
		var iSelectedAccount = elem.data("account");

		return iSelectedAccount;
	}

	$(document).ready(function() {
		$(".reg_slidein_info .hide_button").click(function() {
			$(".reg_slidein_info").addClass("away");
		});

		var accountSymbols = document.getElementsByClassName("regTableTradingAccountRow");

		<?php if($showUpToInfo < 1): ?>
			document.getElementById("regTableInfo1").addEventListener("click", function() {
				regTableInfoClicked(1);
			});
			accountSymbols[0].addEventListener("click", function(event) {selectAccount('basic')});
		<?php endif; ?>

		
		<?php if($showUpToInfo < 2): ?>
			document.getElementById("regTableInfo2").addEventListener("click", function() {
				regTableInfoClicked(2);
			});
			accountSymbols[1].addEventListener("click", function(event) {selectAccount('plus')});
		<?php endif; ?>

		<?php if($showUpToInfo < 3): ?>
			document.getElementById("regTableInfo3").addEventListener("click", function() {
				regTableInfoClicked(3);
			});
			accountSymbols[2].addEventListener("click", function(event) {selectAccount('pro')});
		<?php endif; ?>


		<?php if($showUpToInfo < 4): ?>
			document.getElementById("regTableInfo4").addEventListener("click", function() {
				regTableInfoClicked(4);
			});
			accountSymbols[3].addEventListener("click", function(event) {selectAccount('pro_plus')});
		<?php endif; ?>


	})
</script>