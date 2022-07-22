<?php

$result = [];

try {
    include("configuration.php");
    include("db.php");

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //
    $temporaryCode = trim($_REQUEST["temporaryCode"]);

    //testmode (LTCT Currency)
    if (substr($temporaryCode, 0, 2) == "L:")
    {
        session_start(); // start or continue a session
        $_SESSION['test_currency'] = true;
        $temporaryCode = substr($temporaryCode,2);
    }
    //
    $result["message"] = "";

    if (!$temporaryCode)
        $result["message"] .= "Parameter temporaryCode missing. ";

    $result["message"] = trim($result["message"]);
    if ($result["message"]) {
        $result["code"] = "-1";
        $jsonOut = json_encode($result);
        die($jsonOut);
    }

    $pdo->beginTransaction();
    $result = checkTemporaryCode($pdo, $temporaryCode);
    if($result["code"] < 0)
        $pdo->rollback();
    else
        $pdo->commit();

    //
    /*
    $sql = "SELECT * FROM TemporaryEntryCodes WHERE code=:code";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(':code', $temporaryCode);
    if (!$sth->execute()) {
        $msg = "Error: " . $sth->errorInfo()[2];
        error_log($msg);
        $result["code"] = -2;
        $result["message"] = $msg;
        die(json_encode($result));
    }
    $rowCode = $sth->fetch(PDO::FETCH_ASSOC);
    if ($rowCode) {
        $timeCodeDate = strtotime($rowCode["date"]);
        $timeCodeValidUntil = $timeCodeDate + DURATION_TEMPORARY_CODE_VALID_SECONDS;
        if (time() <= $timeCodeValidUntil) {
            if ($rowCode["user_id"]) {
                $result["code"] = -3;
                $result["message"] = "Code in use";
            } else {
                $result["code"] = 1;
                $result["message"] = "Ok";
            }
        } else {
            $result["code"] = -4;
            $result["message"] = "Invalid code";
        }
    } else {
        $result["code"] = -5;
        $result["message"] = "Unknown code";
    }
    */


    $jsonOut = json_encode($result);
    die($jsonOut);
} catch (Exception $e) {
    $result["code"] = "-2";
    $result["message"] = $e->getMessage();
    $jsonOut = json_encode($result);
    die($jsonOut);
}
