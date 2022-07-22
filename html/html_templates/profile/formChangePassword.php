<div class="profile_modal_change_password">
    <!-- Modal content -->
    <div class="profile_modal_change_password_content">
    
        <div id="loadingMarqueePassword" class="loading_overlay" style="display: none;">
            <img src="./Images/loading.gif" alt="">
        </div>
        <form id="changePasswordForm">
            <div class="text_modal mb-2">
                <?= localize('bo_profile_old_password_header', $user['language']) ?>
            </div>
            <div class="d-flex flex-row">
                <input class="profile_input input_text mb-3 placeholder_input" autocomplete="old-password" type="password" name="oldPassword" placeholder="<?= localize('bo_profile_placeholder_old_password', $user['language']) ?>" required>
            </div>
            <div class="bo_separator mb-5"></div>
            <div class="text_modal mb-2 mt-3">
                <?= localize('bo_profile_create_new_password_header', $user['language']) ?>
            </div>
            <div class="d-flex flex-row mb-4">
                <input id="newPassword" class="flex-grow-1 profile_input input_text mb-3 placeholder_input" autocomplete="new-password" type="password" name="newPassword" placeholder="<?= localize('bo_profile_placeholder_new_password', $user['language']) ?>" style="padding-right: 55px;" required>
                <div id="showPassword" class="align-self-center mb-3 showPasswordButton">&#x1F441</div>
            </div>
            <div class="text_modal mb-2">
                <?= localize('bo_profile_repeat_password_header', $user['language']) ?>
            </div>
            <div class="d-flex flex-row">
                <input class="profile_input input_text mb-4 placeholder_input" autocomplete="new-password" type="password" name="newPasswordRepeat" placeholder="<?= localize('bo_profile_placeholder_new_password_repeat', $user['language']) ?>" required>
            </div>
            <div id="PasswordIsNotTheSameText" class="d-flex flex-row red" style="display: none !important;"><?= localize('bo_profile_password_not_identical', $user['language']) ?></div>

            <div class="mt-5">
                <?php include("./html_templates/profile/buttonSaveDiscardGroup.php") ?>
            </div>
        </form>
    </div>
</div>