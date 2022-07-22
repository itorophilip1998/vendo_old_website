<?php

require_once("configuration.php");
require_once(__DIR__  . "/lib/commissions.php");
require_once("bitcoin.php");
require_once("enums.php");

function calculateCommissions()
{
    $commissions_calculator = new Commissions;

    //get all OpenBuyingtransactions with status 'complete' that haven't yet been commissioned
    $transactions = Bitcoin::getUncommissionedTransactions(COMMISSION_DELAY);

    foreach($transactions as $transaction)
    {
        //assign commission from the transaction
        if ($transaction['type'] == PaymentType::ACCESS)
        {
            $source = Commissions::COMMISSION_SOURCE_ACCESS;
            $original_amount = $transaction['amount'] - REGISTRATION_FEE; //initial registration fee does not enter into provision
            $product = $transaction['product'];
        }
        else if ($transaction['type'] == PaymentType::UPGRADE)
        {
            $source = Commissions::COMMISSION_SOURCE_UPGRADE;
            $original_amount = $transaction['amount'];
            $product = 'Upgrade '.$transaction['product'];
        }
        else{
            //old payment system - will not be provisioned here
            continue;
        }
        $commissions_calculator->assignCommissions($transaction['user_id'], $original_amount, $product, $source);

        //mark transaction as commissioned
        Bitcoin::setTransactionCommissioned($transaction['id']);
    }
}

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

    calculateCommissions();

    sem_release($semaphore);
}
else{
    echo "Secret!";
}

?>