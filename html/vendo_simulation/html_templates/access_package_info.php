<?php
$showUpToInfo = 0;
$amountAccount1 = ACCOUNT_1_FEE;
$amountAccount2 = ACCOUNT_2_FEE;
$amountAccount3 = ACCOUNT_3_FEE;
$amountAccount4 = ACCOUNT_4_FEE;

if(isset($user)) {
    $showUpToInfo = $user['trading_account'];

    $amountPaid = getOriginalPaidAmountFromAccess($user['trading_account']);

    $amountAccount1 = ACCOUNT_1_FEE - $amountPaid;
    $amountAccount2 = ACCOUNT_2_FEE - $amountPaid;
    $amountAccount3 = ACCOUNT_3_FEE - $amountPaid;
    $amountAccount4 = ACCOUNT_4_FEE - $amountPaid;
}

?>

<div class="reg_slidein_info away">
    <div class="resizeable">
        <div class="description"><?php echo localize('reg5_slidein_description'); ?></div>

        <?php if($showUpToInfo < 1): ?>
            <div class="trade_account_info acc_type basic">
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_suggested_capital'); ?></div>
                    <div class="info">$ <?php echo number_format(ACCOUNT_1_AMOUNT, 0, '.', ','); ?></div>
                </div>
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_performance_fee'); ?></div>
                    <div class="info">
                        <div class="beige textLarge">35.0%</div>
                        <div><?php echo localize('per_month'); ?></div>
                    </div>
                </div>
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_management_fee'); ?></div>
                    <div class="info">
                        <div class="textLarge">0.16%</div>
                        <div><?php echo localize('per_month'); ?></div>
                    </div>
                </div>
                <div class="cell highlight">
                    <div class="label"><?php echo localize('reg5_info_member_fee'); ?></div>
                    <div class="info">
                        <div class="green textLarge">$ <?php echo number_format($amountAccount1, 0, '.', ','); ?></div>
                        <div><?php echo localize('one_time'); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($showUpToInfo < 2): ?>
            <div class="trade_account_info acc_type plus">
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_suggested_capital'); ?></div>
                    <div class="info">$ <?php echo number_format(ACCOUNT_2_AMOUNT, 0, '.', ','); ?></div>
                </div>
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_performance_fee'); ?></div>
                    <div class="info">
                        <div class="beige textLarge">32.0%</div>
                        <div><?php echo localize('per_month'); ?></div>
                    </div>
                </div>
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_management_fee'); ?></div>
                    <div class="info">
                        <div class="textLarge">0.16%</div>
                        <div><?php echo localize('per_month'); ?></div>
                    </div>
                </div>
                <div class="cell highlight">
                    <div class="label"><?php echo localize('reg5_info_member_fee'); ?></div>
                    <div class="info">
                        <div class="green textLarge">$ <?php echo number_format($amountAccount2, 0, '.', ','); ?></div>
                        <div><?php echo localize('one_time'); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($showUpToInfo < 3): ?>
            <div class="trade_account_info acc_type pro">
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_suggested_capital'); ?></div>
                    <div class="info">$ <?php echo number_format(ACCOUNT_3_AMOUNT, 0, '.', ','); ?></div>
                </div>
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_performance_fee'); ?></div>
                    <div class="info">
                        <div class="beige textLarge">28.0%</div>
                        <div><?php echo localize('per_month'); ?></div>
                    </div>
                </div>
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_management_fee'); ?></div>
                    <div class="info">
                        <div class="textLarge">0.16%</div>
                        <div><?php echo localize('per_month'); ?></div>
                    </div>
                </div>
                <div class="cell highlight">
                    <div class="label"><?php echo localize('reg5_info_member_fee'); ?></div>
                    <div class="info">
                        <div class="green textLarge">$ <?php echo number_format($amountAccount3, 0, '.', ','); ?></div>
                        <div><?php echo localize('one_time'); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        
        <?php if($showUpToInfo < 4): ?>
            <div class="trade_account_info acc_type pro_plus">
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_suggested_capital'); ?></div>
                    <div class="info">$ <?php echo number_format(ACCOUNT_4_AMOUNT, 0, '.', ','); ?></div>
                </div>
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_performance_fee'); ?></div>
                    <div class="info">
                        <div class="beige textLarge">25.0%</div>
                        <div><?php echo localize('per_month'); ?></div>
                    </div>
                </div>
                <div class="cell">
                    <div class="label"><?php echo localize('reg5_info_management_fee'); ?></div>
                    <div class="info">
                        <div class="textLarge">0.16%</div>
                        <div><?php echo localize('per_month'); ?></div>
                    </div>
                </div>
                <div class="cell highlight">
                    <div class="label"><?php echo localize('reg5_info_member_fee'); ?></div>
                    <div class="info">
                        <div class="green textLarge">$ <?php echo number_format($amountAccount4, 0, '.', ','); ?></div>
                        <div><?php echo localize('one_time'); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <div class="hide_button"><i class="fas fa-chevron-left"></i></div>
</div>