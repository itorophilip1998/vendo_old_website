            <div class="textLarger :classTextBitcoin2 ">
				:textBitcoinAmount 
			</div>

			<div class="verticalSpacer"></div>

			<div class=":hidden_if_fail">
				<div>
					<?php echo $bc_address; ?>
				</div>
				<div class="verticalSpacer"></div>
				<div>
					:bc_address
				</div>

				<div class="verticalSpacer"></div>
				<div class="verticalSpacer"></div>
        </div>

			<div class="bitcoin_qr_code">
				<image src=":qr_code_url" alt=":bc_address"/>
			</div>