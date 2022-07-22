<?php

require_once("configuration.php");
require_once(__DIR__  . "/lib/commissions.php");
require_once("bitcoin.php");
require_once("enums.php");

$options = getopt("s::");
$secret = isset($options['s'])?$options['s']:false;

if ($secret === CRON_SECRET)
{
    $key = 156478953;
    $maxAcquire = 1;
    $permissions =0666;
    $autoRelease = 1;
    
    $semaphore = sem_get($key, $maxAcquire, $permissions, $autoRelease);
    sem_acquire($semaphore);  //blocking (prevent simultaneous multiple executions)

    $commissions = new Commissions();
    $commissions->recalculateRank();

    sem_release($semaphore);
}
else{
    echo "Secret!";
}

?>