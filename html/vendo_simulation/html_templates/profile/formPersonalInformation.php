<form id="PersonalInformationForm" action="" >
    <div class="d-flex flex-row">
        <input class="profile_input input_text mb-3 mr-3" type="text" name="given_name" value="<?= $user['given_name'] ?>" required>
        <input class="profile_input input_text mb-3 ml-3" type="text" name="sur_name" value="<?= $user['sur_name'] ?>" required>
    </div>

    <div class="row mb-3">
        <div class="col col-md-6">
            <label class="container_radio widthRadioSex input_text"> <input required type="radio"
                name="sex" value="0" class="pointer"
                <?php if($user['sex'] == 0): ?>
                    checked
                <?php endif; ?>
                > <span
                id="radio_sex_0" class="checkmark pointer"></span>
                <span class="span_text"><?php echo localize("reg1_sex_male", $user['language']); ?></span>
            </label>
        </div>
        <div class="col col-md-6">
            <label class="container_radio widthRadioSex input_text"> <input required type="radio"
                name="sex" value="1" class="pointer"
                <?php if($user['sex'] == 1): ?>
                    checked
                <?php endif; ?>
                > <span
                id="radio_sex_1" class="checkmark pointer"></span>
                <span class="span_text"><?php echo localize("reg1_sex_female", $user['language']); ?></span>
            </label>
        </div>
    </div>


    <input class="profile_input input_text mb-3" type="date" name="date_of_birth" value="<?= $user['date_of_birth'] ?>" required placeholder="yyyy-mm-dd">
    <?php include("./html_templates/profile/buttonSaveDiscardGroup.php") ?>
</form>
