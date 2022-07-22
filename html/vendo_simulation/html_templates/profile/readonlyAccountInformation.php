<div class="bo_profile_text">
    <?= getLanguageReadable($user['language']) ?>
</div>
<div class="bo_profile_text">
    <?= strtoupper(getNameTradingAccount($user["trading_account"])) ?>
</div>
<div class="bo_profile_text">
    <?= $user['email'] ?>
</div>
<div class="bo_profile_text">
    <?= ((!empty($countryPhoneCode['phonecode']))?("+".$countryPhoneCode['phonecode'] . " "):"") . $user['mobile_number'] ?>
</div>
<div class="bo_profile_text">
    <?php echo localize("bo_profile_payout_address"); ?>: <?= $user['payout_address'] ?> 
</div>
