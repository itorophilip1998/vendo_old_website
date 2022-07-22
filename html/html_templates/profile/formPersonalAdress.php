<form id="changePersonalAdressForm" action="">
    <select class="profile_input mb-3" name="country" id="countrySelect" form="changePersonalAdressForm">
        <?php foreach($countries as $country): ?>
            <option value="<?= $country['iso'] ?>"
            
            <?php 
                if(strtoupper($user['country']) == strtoupper($country['iso'])) {
                    echo "selected";
                }
            ?>
            
            ><?= $country['name'] ?></option>
        <?php endforeach; ?>
    </select>
    
    <div class="d-flex flex-row">
        <input class="profile_input input_text mb-3 mr-3" type="text" name="city" value="<?= $user['city'] ?>" required>
        <input class="profile_input input_text mb-3 ml-3" type="text" name="postcode" value="<?= $user['postcode'] ?>" required>
    </div>
    
    <div class="d-flex flex-row">
        <input class="profile_input input_text mb-3 mr-3" type="text" name="housenumber" value="<?= $user['housenumber'] ?>" required>
        <input class="profile_input input_text mb-3 ml-3" type="text" name="street" value="<?= $user['street'] ?>" required>
    </div>
    <?php include("./html_templates/profile/buttonSaveDiscardGroup.php") ?>
</form>
