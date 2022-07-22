<?php

include_once("translate.php");
include_once("configuration.php");
include_once("db.php");
include_once("utils.php");
include_once("enums.php");
include_once("lib/commissions.php");


$pdo = getDatabase();

try {
    $daysago = COMMISSION_DELAY;
    $sql = "SELECT * FROM (
                SELECT `user_id`,`amount`,`started_on` as `date`,`product`, 'payment' as type FROM `OpenBuyingTransactions` WHERE `product` != 'withdrawal' AND status='complete' AND DATE_ADD(started_on, INTERVAL :daysago DAY) <= NOW()
                UNION
                SELECT `user_id`,`amount`, `date`,'Performance Fee' as `product`, 'performance' as type FROM PerformanceFee WHERE 1
            ) as a ORDER BY a.`date`";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(':daysago', $daysago);
    if (!$sth->execute()) {
        return NULL;
    }
    
    $transactions = $sth->fetchAll(PDO::FETCH_ASSOC);  

    $users_with_access = array();
    $commissions_calculator = new Commissions();
    foreach($transactions as $transaction)
    {
        $source = Commissions::COMMISSION_SOURCE_ACCESS;
        $amount = $transaction['amount'];
        if ($transaction['type'] == 'performance')
        {
            //performance fee
            $source = Commissions::COMMISSION_SOURCE_BROKER;
        }
        else
        {
            if (array_search($transaction['user_id'], $users_with_access) === false) //if this is the first access payment for this user, than it is access and not upgrade
            {
                //access -> add user to the list
                updateUser($pdo, $transaction['user_id'], array('broker_registration_complete' => '1'));
                $users_with_access[] = $transaction['user_id'];
                $amount = ($amount > REGISTRATION_FEE)?$amount - REGISTRATION_FEE:0; //substract registration fee from the first access payment
            }
            else
            {
                //upgrade
                $source = Commissions::COMMISSION_SOURCE_UPGRADE;
            }
        }
        $commissions_calculator->assignCommissions($transaction['user_id'], $amount, $transaction['product'], $source);
        $commissions_calculator->confirmCommissions($transaction['user_id']);
    }

    echo "Finished.";
} catch (Exception $e) {
    $msg = "exception: " . $e->getMessage();
    echo $msg;
    error_log($msg);
    die();
}

?>

<html>

<head>
</head>

<body>
</body>

</html>