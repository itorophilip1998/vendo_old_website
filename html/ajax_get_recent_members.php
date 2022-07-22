<?php

$result = [];

try {
    include("configuration.php");
    include("db.php");

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $users = readRecentMembers($pdo);

    $numMembers = getCountMembers($pdo);

    $result["code"] = 1;
    $result["users"] = $users;
    $result["numMembers"] = $numMembers;

    $jsonOut = json_encode($result);
    die($jsonOut);

} catch (Exception $e) {
    $result["code"] = "-2";
    $result["message"] = $e->getMessage();
    $jsonOut = json_encode($result);
    die($jsonOut);
}

?>