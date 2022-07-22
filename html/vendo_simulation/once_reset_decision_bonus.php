<?php

require_once("configuration.php");
require_once("db.php");

$options = getopt("s::");
$secret = isset($options['s'])?$options['s']:false;

if ($secret === CRON_SECRET)
{
    $pdo = getDatabase();

    $sql = "UPDATE `vendo`.`User` SET `decision_start` = '2020-08-15 00:00:00', decision_bonus_time_over = 0 WHERE 1;";

    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    $sth->execute();
}
else{
    echo "Secret!";
}

?>