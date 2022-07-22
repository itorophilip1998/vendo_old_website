<?php
include_once("translate.php");
include_once("configuration.php");
include_once("utils.php");
include_once("bitcoin.php");

try {

	$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	include 'bo_read_user_logged_in.php';

	if (!$user['is_admin'])
	{
		//silent fail 
		redirect(ROOT_URL.'bo_main.php');
	}

	$pendingTransactions = Bitcoin::getAllPendingTransactions();
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
	
	<div id="divToBlur">
		<!-- navbar -->
		<div>
			<?php
			include("bo_navbar.php");
			?>
		</div>


		<div class="main-color">
			<h2>
				<?php echo localize("bo_pending_bitcoins_title"); ?>
			</h2>

			<div class="<?php if (count($pendingTransactions) > 0) echo "hidden"; ?>">
				<?php echo localize("bo_pending_bitcoins_nothing"); ?>
			</div>

			<div class="col <?php if (count($pendingTransactions) == 0) echo "hidden"; ?>" id="table-lots">
				<table id="table-transactions" class="table table-sm mt-3 table-borderless align-self-center">
					<thead class="bo_text_header table_bottom_line">
						<tr id="table-transactions-header">
							<th><?php echo localize("bo_pending_bitcoins_table_date"); ?></th>
							<th><?php echo localize("bo_pending_bitcoins_table_user_name"); ?></th>
							<th><?php echo localize("bo_pending_bitcoins_table_product_name"); ?></th>
							<th><?php echo localize("bo_pending_bitcoins_table_price_usd"); ?></th>
							<th><?php echo localize("bo_pending_bitcoins_table_price_bitcoins"); ?></th>
							<th><?php echo localize("bo_pending_bitcoins_table_currency"); ?></th>
							<th>Status</th>
							<th>Check Status</th>
							<th></th>
						</tr>
					</thead>
					<tbody id="table-transactions-body" class="bo_text_table_body">
						<?php foreach ($pendingTransactions as $pendingTransaction) : ?>
							
							<tr>
								
								<td>
									<?php echo $pendingTransaction["started_on"]; ?>
								</td>								
								<td>
									<?php echo $pendingTransaction["user_name"]; ?>
								</td>
								<td>
									<?php echo $pendingTransaction["product"]; ?>
								</td>
								<td>
									<?php echo $pendingTransaction["amount"]; ?>
								</td>
								<td>
									<?php echo $pendingTransaction["api_amount"]; ?>
								</td>
								<td>
									<?php echo $pendingTransaction["currency"]; ?>
								</td>								
								<td>
									<?php echo $pendingTransaction["status"]; ?>
								</td>								
								<td>
									<a href="<?php echo $pendingTransaction["status_url"]; ?>" target="_blank">status</a>
								</td>
								<td>
									<a class="pointer"
									onclick="finishBitcoinTransaction(event, '<?php echo $pendingTransaction["id"]; ?>')"
									alt="" height=16 width=16 title="<?php echo localize("bo_pending_bitcoins_table_finish_bitcoins"); ?>"><i class="fab fa-bitcoin"></i></a>
								</img>
								</td>
							</tr>
						<?php endforeach; ?>						
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div id="modal_confirm_finish" class="modal_ta" style="position: fixed">
	  	<div class="modal_ta_content">
			<div class="verticalSpacer"></div>

			<div id="divInfoText" class="text_modal center_text center_horizontally w80">

				<div id="label_finish_conf"><?php echo localize("bo_pending_bitcoins_ask_confirm"); ?></div>
				<div class="verticalSpacer"></div>
				<div style="display: inline-block;">
					<input id="input_dummy_finish_conf_transaction_id" type='hidden' value=''/>		
					<input type="button" class="button_filled button_transparent inline button_text_modal_orange button_modal_outline no_shadow" id="button_finish_confirm_affirm" value="<?php echo localize("general_yes"); ?>"/>
					<input type="button" class="div_modal_warning_close button_filled inline button_text_modal_black" id="button_finish_confirm_reject" value="<?php echo localize("general_no"); ?>"/>
				</div>
			</div>
	  	</div>
	</div>

	<script>
	    window.onload = function() {
            document.getElementById("nav_admin").classList.add("active");
        }

		function finishBitcoinTransaction(event, transactionId) {
			if(transactionId) {
				$("#input_dummy_finish_conf_transaction_id").val(transactionId);				
				$("#modal_confirm_finish").show();
			}
		}

		$("#button_finish_confirm_affirm").click(function () {
			var transactionId = $("#input_dummy_finish_conf_transaction_id").val();
			$.ajax("ajax_finish_bitcoin_transaction.php",{
					data: { transactionId: transactionId },
					dataType: "json"
			})
			.done(function (data) {
					if (data.code == "1") {
						location.reload();
					} else {
						alert("Server error '" + data.message + "'");
						location.reload();
					}
			})
			.fail(function(xhr, status, error) {
				console.log(xhr);
				console.log(status);
				console.log(error);
				alert("Connection/server error");
			})
			.always(function(xhr, status, error) {
				$("#modal_confirm_finish").css("display", "none");
			})
		});

		$("#button_finish_confirm_reject").click(function () {
			$("#modal_confirm_finish").css("display", "none");
		});

		$("#span_finish_conf").click(function () {
			$("#modal_confirm_finish").css("display", "none");
		});
		
		/*
		// When the user clicks anywhere outside of a modal ...
		window.onclick = function(event) {
			if (event.target.classList.contains("modal")) {
				window.location.reload(false);
				// hideModals(true);
			}
		}
		
		window.addEventListener("keyup", function(event) {
			event.preventDefault();
			if (event.keyCode === 27) {
				$("#modal_confirm_finish").css("display", "none");
			}
		});

		*/

	</script>

</body>

?>