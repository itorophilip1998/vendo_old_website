<?php

include_once("translate.php");
include_once("configuration.php");
include_once("db.php");
include_once("utils.php");
include_once("enums.php");
include_once("lib/commissions.php");


$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

include 'bo_read_user_logged_in.php';
// is logged in

try {
    $pdo = getDatabase();
    $sql = "SELECT user_id, amount, product FROM OpenBuyingTransactions WHERE status='complete' AND amount > 60;";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    //$sth->bindParam(':userIdNode', $userIdNode);
    if (!$sth->execute()) {
        return NULL;
    }
    
    $upgrades = $sth->fetchAll(PDO::FETCH_ASSOC);  

    $commissions_calculator = new Commissions;
    foreach($upgrades as $upgrade)
    {
        $commissions_calculator->assignCommissions($upgrade['user_id'], $upgrade['amount'], 'Upgrade '.$upgrade['product'], Commissions::COMMISSION_SOURCE_UPGRADE);
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