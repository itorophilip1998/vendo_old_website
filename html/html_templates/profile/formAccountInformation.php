<form id="changeAccountInfoForm" action="">
    <select id="langaugeSelect" class="profile_input mb-3" name="language" form="changeAccountInfoForm" required>
            <option value="de"<?php if(strtoupper($user['language']) == "DE") { echo " selected"; } ?>><?= getLanguageReadable("de") ?></option>
            <option value="en"<?php if(strtoupper($user['language']) == "EN") { echo " selected"; } ?>><?= getLanguageReadable("en") ?></option>
    </select>
    <?php if(($user['trading_account'] >= 1 && $user['trading_account'] < 4) && $user['broker_registration_complete'] == 1): ?>
        <div>
            <input class="profile_input input_text mb-3 w30 center_text" type="text" name="access" readonly value="<?= strtoupper(getNameTradingAccount($user["trading_account"])) ?>">
            <a href="./access.php" class="bo_profile_title_edit profile_upgrade_access no_link_decoration">
            Upgrade
            </a>

        </div>
    <?php endif; ?>
    <!-- <input class="profile_input input_text mb-3" type="email" name="email" value="$user['email']" required> -->
    <a id="changePassword" class="bo_profile_title_edit mb-3 no_link_decoration" href="#"><?= $changePasswortText ?></a>

    <div class="row mb-3 mt-3">
        <div class="col-md-4">
            <?= createPhoneCodeHtmlSelect($_SESSION["locale"], "profile_input", $user['phonecode']) ?>
        </div>
        <div class="col-md-8">
            <input class="profile_input input_text" type="text" name="mobile_number" value="<?= $user['mobile_number'] ?>" required>
        </div>
    </div>

    <div class="row mb-3 mt-3">
        <div class="col-md-4 bo_profile_text">
            <?php echo localize("bo_profile_payout_address"); ?>
        </div>
        <div class="col-md-8">
            <input id="inputPayoutAddress" class="profile_input input_text" name="payout_address" type="text" 
                    value="<?= $user['payout_address'] ?>" 
                    placeholder="<?php echo localize("bo_profile_payout_address_placeholer"); ?>">
            </input>
        </div>
    </div>
    
    <?php include("./html_templates/profile/buttonSaveDiscardGroup.php") ?>
</form>
