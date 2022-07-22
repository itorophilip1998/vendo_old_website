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

    function getTypeName($type)
    {
        switch ($type) {
            case 'affiliate':
                return 'Affiliate';
            case 'unstoppable':
                return 'Unstoppable';
            case 'pool':
                return 'Reverse';
            case 'jackpot':
                return 'Jackpot';
            default:
                return '';
        }

        return '';
    }

    try {
        $commissions_calculator = new Commissions;
        $commissions = $commissions_calculator->getAllCommissionsForUser($user['id']);

        $rowTemplate = file_get_contents("html_templates/commission/commission_downline_row.html");

        $rowSummaryTemplate = file_get_contents("html_templates/commission/commission_summary_row.html");
        /*
        $statistic = $commissions_calculator->getStatisticsForUser($user['id']);
        foreach ($statistic as $value) {
            if($value['status'] == Commissions::COMMISSION_STATUS_PENDING) {
                $rowSummaryTemplate = str_replace(":onHold", number_format($value['amount'], 2, ",", "."), $rowSummaryTemplate);
            } else if($value['status'] == Commissions::COMMISSION_STATUS_CONFIRMED){
                $rowSummaryTemplate = str_replace(":earned", number_format($value['amount'], 2, ",", "."), $rowSummaryTemplate);
            }
        }
        // if there are no 4 rows for every status - set 0
        if(count($statistic) < 4) {
            $rowSummaryTemplate = str_replace(":onHold", number_format(0, 2, ",", "."), $rowSummaryTemplate);
            $rowSummaryTemplate = str_replace(":paid", number_format(0, 2, ",", "."), $rowSummaryTemplate);
            $rowSummaryTemplate = str_replace(":earned", number_format(0, 2, ",", "."), $rowSummaryTemplate);
            $rowSummaryTemplate = str_replace(":available", number_format(0, 2, ",", "."), $rowSummaryTemplate);
        }
        */
        $commission_ids = "";
        $earned = $commissions_calculator->getBalance($user['id'], Commissions::COMMISSION_CURRENCY_USD, Commissions::COMMISSION_STATUS_CONFIRMED, Commissions::PARAM_IGNORE, /*out*/ $commission_ids);
        $onhold = $commissions_calculator->getBalance($user['id'], Commissions::COMMISSION_CURRENCY_USD, Commissions::COMMISSION_STATUS_PENDING, Commissions::PARAM_IGNORE, /*out*/ $commission_ids);
        $available = $commissions_calculator->getBalance($user['id'], Commissions::COMMISSION_CURRENCY_USD, Commissions::COMMISSION_STATUS_CONFIRMED, Commissions::COMMISSION_PAYOUT_OPEN, /*out*/ $commission_ids);
        $paid = $commissions_calculator->getBalance($user['id'], Commissions::COMMISSION_CURRENCY_USD, Commissions::COMMISSION_STATUS_CONFIRMED, Commissions::COMMISSION_PAYOUT_PAID, /*out*/ $commission_ids);

        $rowSummaryTemplate = str_replace(":earned", number_format($earned, 2, ",", "."), $rowSummaryTemplate);        
        $rowSummaryTemplate = str_replace(":onHold", number_format($onhold, 2, ",", "."), $rowSummaryTemplate);
        $rowSummaryTemplate = str_replace(":available", number_format($available, 2, ",", "."), $rowSummaryTemplate);
        $rowSummaryTemplate = str_replace(":paid", number_format($paid, 2, ",", "."), $rowSummaryTemplate);


        $completeTableBody = "";
        foreach ($commissions as $singleCommission) {
            $newRow = $rowTemplate;

            $type = getTypeName($singleCommission['type']);

            $newRow = str_replace(":customer", $singleCommission['source_partner_name'], $newRow);
            $newRow = str_replace(":access", $singleCommission['product'], $newRow);
            $newRow = str_replace(":price", number_format($singleCommission['commissionable_amount'], 2), $newRow);
            $newRow = str_replace(":direct_partner", $singleCommission['direct_partner_name'], $newRow);
            $newRow = str_replace(":type",  $type, $newRow);
            $newRow = str_replace(":level", $singleCommission['depth'], $newRow);
            $newRow = str_replace(":commission", number_format($singleCommission['applied_percent'] * 100, 2), $newRow);

            $amount = $singleCommission['amount'];
            $class = "";
            if($amount > 0) {
                $class = "green";
            } else if($amount < 0) {
                $class = "red";
            }
            $newRow = str_replace(":class_amount", $class, $newRow);
            $newRow = str_replace(":amount", number_format($amount, 2), $newRow);

            if ($singleCommission['paid_out'])
            {
                $status = 'paid';
            }
            else
            {
                $status = $singleCommission['status'];

            }
            
            $class = "green";
            if($status == "pending") {
                $class = "red";
            }            
            
            $status_text = localize("bo_commission_status_".$status);
            $newRow = str_replace(":class_status", $class, $newRow);
            $newRow = str_replace(":status", $status_text, $newRow);
            
            $completeTableBody .= $newRow;
        }

        echo json_encode([
            'commissions' => $completeTableBody,
            'summary' => $rowSummaryTemplate,
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