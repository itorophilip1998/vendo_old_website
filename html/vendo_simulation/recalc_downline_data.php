<?php

include_once("translate.php");
include_once("configuration.php");
include_once("db.php");
include_once("utils.php");
include_once("enums.php");


$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

include 'bo_read_user_logged_in.php';
// is logged in

try {
    // RECALCULATE DOWNLINE DATA OF WHOLE MEMBER TREE
    $sponsorTreeAll = loadSponsorTreeByReadingWholeUserTable($pdo, 1);
    writeDownlineInfoCalculatedOfTree($pdo, $sponsorTreeAll);

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