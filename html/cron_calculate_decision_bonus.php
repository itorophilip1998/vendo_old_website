<?php

require_once("configuration.php");
require_once(__DIR__  . "/lib/commissions.php");

$options = getopt("s::");
$secret = isset($options['s'])?$options['s']:false;

if ($secret === CRON_SECRET)
{
    $commissions_calculator = new Commissions;
    $commissions_calculator->recalculateDecisionBonus();
}
else{
    echo "Secret!";
}

?>