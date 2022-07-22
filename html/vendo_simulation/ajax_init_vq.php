<?php
    include_once("configuration.php");
    include_once("db.php");
    include_once("translate.php");
    include_once("utils.php");
    include_once(__DIR__ . "/lib/commissions.php");

    session_start(); // start or continue a session
    $loggedInAsSponsorId = $_SESSION["loggedInAsUserId"];
    if (!$loggedInAsSponsorId) {
        $result["message"] = "Your session has expired, please log in again.";
        $result["code"] = -10;
        $jsonOut=json_encode($result);		
        die($jsonOut);            
    }
    
    $pdo = getDatabase();

    include 'bo_read_user_logged_in.php';
    // is logged in

    try {
        $vqs = getAllVQsOfUser($pdo, $user['id']);

        //$rowTemplate = file_get_contents("html_templates/commission/commission_downline_row.html");
        $rowTemplate = file_get_contents("html_templates/vq/vq_table_row.html");

        $completeTableBody = "";
        $sum = 0;
        foreach ($vqs as $singleVQ) {
            $newRow = $rowTemplate;

            $newRow = str_replace(":timeUTC", $singleVQ['created_on'], $newRow);
            $newRow = str_replace(":amount", $singleVQ['amount'], $newRow);
            $textType = localize("bo_vq_type_".$singleVQ['type']);
            $newRow = str_replace(":type", $textType, $newRow);

            $completeTableBody .= $newRow;

            $sum += $singleVQ['amount'];
        }

        echo json_encode([
            'vqs' => $completeTableBody,
            'sum' => $sum,
            'code' => 200
        ]);


    } catch (Exception $ex) {
        echo json_encode([
            'commissions' => [],
            'code' => -3,
            'message' => $ex->getMessage()
        ]);
    }

    die();
    ?>