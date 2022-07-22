<div class="bo_profile_text">
    <?= $user['given_name'] . " " . $user['sur_name'] ?>
</div>
<div class="bo_profile_text">
    <?= getSexFromUser($user['sex'], $user['language']) ?>
</div>
<div class="bo_profile_text">
    <?= date("d.m.Y", strtotime($user['date_of_birth'])) ?>
</div>