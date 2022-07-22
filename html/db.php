<?php


function getDatabase()
{
    static $pdo = null;
    if (!$pdo)
    {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    return $pdo;
}

function checkTemporaryCode($pdo, $temporaryCode, $timeToCheck = NULL)
{        
    $result["code"] = 1;
    $result["message"] = "";

        //testmode (LTCT Currency)
        if (substr($temporaryCode, 0, 2) == "L:")
        {
            $temporaryCode = substr($temporaryCode,2);
        }

    if(!$pdo->inTransaction()) {
        $result["code"] = -99;
        $result["message"] = "Must be called within transaction";
        return $result;
    }

    if (!$timeToCheck)
        $timeToCheck = time();
    $result["date_time_checked"] = date("Y-m-d H:i:s", $timeToCheck);

    $sql = "SELECT * FROM TemporaryEntryCodes WHERE code=:code";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(':code', $temporaryCode);
    if (!$sth->execute()) {
        $msg = "Error: " . $sth->errorInfo()[2];
        error_log($msg);
        $result["code"] = -2;
        $result["message"] = $msg;
    } else {
        $rowCode = $sth->fetch(PDO::FETCH_ASSOC);
        if ($rowCode) {
            $timeCodeDate = strtotime($rowCode["date"]);
            $timeCodeValidUntil = $timeCodeDate + DURATION_TEMPORARY_CODE_VALID_SECONDS;
            if ($rowCode["user_id"]) {
                $result["code"] = -3;
                $result["message"] = "Code was used by a member";   
            } else if ($rowCode["date_checked"] && $rowCode["date_checked"] != date("Y-m-d H:i:s", $timeToCheck)) {
                $result["code"] = -7;
                $result["message"] = "Code was validated";
            } else {
                if ($timeToCheck > $timeCodeValidUntil) {
                    $result["code"] = -4;
                    $result["message"] = "Code expired";
                } else {
                    $sql = "UPDATE TemporaryEntryCodes SET date_checked=:dateChecked WHERE id=:idCode";
                    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                    $sth->bindParam(':dateChecked', date("Y-m-d H:i:s", $timeToCheck));
                    $sth->bindParam(':idCode', $rowCode["id"]);
                    if (!$sth->execute()) {
                        $msg = "Error: " . $sth->errorInfo()[2];
                        error_log($msg);
                        $result["code"] = -6;
                        $result["message"] = $msg;
                    } else {
                        $result["data"] = &$rowCode;
                        $result["code"] = 1;
                        $result["message"] = "Ok";
                    }
                }
            }
        } else {
            $result["code"] = -5;
            $result["message"] = "Unknown code";
        }
    }

    return $result;
}


function readUser($pdo, $userId)
{
    $sql = "SELECT * FROM User " .
        "WHERE " .
        "id=:userId";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(':userId', $userId);

    if (!$sth->execute()) {
        $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
        error_log($msg);
    }

    $user = $sth->fetch(PDO::FETCH_ASSOC);

    return $user;
}

function readAllRegisteredUsers($pdo)
{
    $sql = "SELECT * FROM User " .
        "WHERE " .
        "broker_registration_complete = 1";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    if (!$sth->execute()) {
        $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
        error_log($msg);
    }

    $user = $sth->fetchAll(PDO::FETCH_ASSOC);

    return $user;
}

function addAccessVolumeAndPropagateInUpline($pdo, $userId, $paidAccessVolume, $originalAccessVolume, $is_init = true) {
        
    $member = readUser($pdo, $userId);

    $parent = readUser($pdo, $member["upline_user_id"]);
    $level = intval($parent['downline_level']) + 1;

    // set for user
    $sql = "UPDATE User SET `access_volume_paid`=`access_volume_paid` + :accessVolume, `access_volume_original`=`access_volume_original` + :origAccessVolume";
    
    // User is not yet initialized (user has just payed for the registration - payment is finished) - set the downline-level for him
    if($is_init) {
        $sql .= ", downline_level=:level";
    }
    $sql .= " WHERE id=:userId;";
    
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(':accessVolume', $paidAccessVolume);
    $sth->bindParam(':origAccessVolume', $originalAccessVolume);
    if($is_init) {
        $sth->bindParam(':level', $level);
    }
    $sth->bindParam(':userId', $userId);

    if (!$sth->execute()) {
        $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
        echo $msg;
        error_log($msg);
        die();
    }

    // propagate up in upline
    $direct = true;
    $member = $parent;
    while ($member) {
        $update_direkt = "";
        if ($direct && $is_init)
        {
            $update_direkt = ", downline_direct_count = downline_direct_count + 1 ";
        }
        $sql = "UPDATE User SET `access_downline_total`=`access_downline_total`+:accessVolume";

        if($is_init) {
            $sql .= ", downline_total_count = downline_total_count + 1";
        }

        $sql .= "$update_direkt WHERE id=:userId";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':accessVolume', $originalAccessVolume);
        $sth->bindParam(':userId', $member["id"]);
    
        if (!$sth->execute()) {
            $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
            echo $msg;
            error_log($msg);
            die();
        }
    
        $member = readUser($pdo, $member["upline_user_id"]);
        $direct = false;
    }
}

function readRecentMembers($pdo)
{
    $sql = "SELECT id, given_name, sur_name, date_of_entry, trading_account FROM User " .
        "WHERE broker_registration_complete=1 ORDER BY date_of_entry DESC LIMIT 30";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    //$sth->bindParam(':userId', $userId);

    if (!$sth->execute()) {
        $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
        echo $msg;
        error_log($msg);
        die();
    }

    $users = $sth->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as &$user) {
        $len = strlen($user["sur_name"]);
        if ($len == 1) {
            $user["sur_name"] = $user["sur_name"] . ".";
        } else if ($len > 1) {
            $user["sur_name"] = substr($user["sur_name"], 0, 1) . ".";
        }

        $user["given_name"] = urlencode($user["given_name"]);
        $user["sur_name"] = urlencode($user["sur_name"]);
    }

    return $users;
}

function _getProfitHistory($userid, $lang, $month, $year) {
    $endOfCurrentMonth = createDateEndOfMonthYear($month, $year);
    
    // go back 1 year
    $year--;
    $month++;
    if($month >= 13) {
        $month = 1;
        $year++;
    }
    
    $startOfLastYear = createDateBeginOfMonthYear($month, $year);
    
    $pdo = getDatabase();

    $sql = "SELECT sum(profit) as profit, MONTH(open_time) as month, YEAR(open_time) as year, COALESCE(Calendar.".strtolower($lang).", Calendar.en, NULL) as translated_name, LEFT(COALESCE(Calendar.".strtolower($lang).", Calendar.en, NULL), 1) as index_name FROM `OrderHistory` 
    JOIN Calendar ON Calendar.id = MONTH(open_time)
    WHERE OrderHistory.`open_time` BETWEEN :begin_time AND :end_time AND userId = :userid AND (OrderHistory.`comment` LIKE '%TradeBot%' OR OrderHistory.`comment` LIKE '%ProconBot%')
    GROUP BY YEAR(open_time), MONTH(open_time), translated_name, index_name ORDER BY year DESC, month DESC";

    try {
        $stmt = $pdo->prepare($sql);
        if($stmt->execute([
            ':begin_time' => $startOfLastYear,
            ':end_time' => $endOfCurrentMonth,
            ':userid' => $userid
        ])) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $th) {
        error_log(print_r($th));
    }
    return null;
}

function getAllMonths($lang) {

    $pdo = getDatabase();
    $sql = "SELECT *, COALESCE(".strtolower($lang).", en, NULL) as translated_name FROM Calendar";

    try {
        $stmt = $pdo->prepare($sql);
        if($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $th) {
        error_log(print_r($th));
    }
    return null;
}

function getCountMembers($pdo) {
    $sql = "SELECT COUNT(id) FROM `User` WHERE 1";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    //$sth->bindParam(':userId', $userId);

    if (!$sth->execute()) {
        $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
        echo $msg;
        error_log($msg);
        die();
    }

    $res = $sth->fetch(PDO::FETCH_ASSOC);

    return $res["COUNT(id)"];
}

function readUserByEmail($pdo, $email)
{
    $sql = "SELECT * FROM User " .
        "WHERE " .
        "email=:email";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(':email', $email);

    if (!$sth->execute()) {
        $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
        echo $msg;
        error_log($msg);
        die();
    }

    $user = $sth->fetch(PDO::FETCH_ASSOC);

    return $user;
}

function loadSponsorTreeChildren($pdo, &$node, $calculateDownline, &$nodesVisited, $max_depth = 20)
{
    $userIdNode = $node["data"]["id"];

    if (in_array($userIdNode, $nodesVisited)) {
        // loop detected
        return $node;
    }
    $nodesVisited[] = $userIdNode;

    $depth = $node["depth"];

    if ($depth >= $max_depth) {
        return $node;
    }

    try {
        $node["children"] = NULL;

        // read children        
        $sql = "SELECT * FROM User " .
            "WHERE upline_user_id=:userIdNode AND broker_registration_complete=1;";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':userIdNode', $userIdNode);
        if (!$sth->execute()) {
            return NULL;
        }
        $childrenFromDb = $sth->fetchAll(PDO::FETCH_ASSOC);

        // process children
        if ($calculateDownline) {
            $node["CountPartnerDirect"] = 0;
            $node["SizeDownline"] = 0;
        }

        foreach ($childrenFromDb as $childFromDb) {
            $userIdChild = $childFromDb["id"];

            if ($node["children"] == NULL)
                $node["children"] = array();

            $children = &$node["children"];
            $children[$userIdChild] = array();
            $child = &$children[$userIdChild];

            $child["data"] = $childFromDb;
            $child["depth"] = $depth + 1;

            if ($calculateDownline) {
                $child["downline_direct_calculated"] = 0;
                $child["downline_all_calculated"] = 0;
            }

            $child["parent"] = &$node;

            if ($calculateDownline) {
                $node["downline_direct_calculated"] += 1;
                $node["downline_all_calculated"] += 1;
            }

            loadSponsorTreeChildren($pdo, $child, $calculateDownline, $nodesVisited, $max_depth);

            if ($calculateDownline) {
                $node["downline_all_calculated"] += $child["downline_all_calculated"];
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }

    return $node;
}


function loadSponsorTree($pdo, $userId, $calculateDownline = false, $max_depth = 20)
{
    try {
        $root = array();

        $root["userId"] = $userId;
        $root["depth"] = 0;

        $nodesVisited = array();

        if ($calculateDownline) {
            $root["downline_direct_calculated"] = 0;
            $root["downline_all_calculated"] = 0;
        }

        $user = readUser($pdo, $userId);

        if ($user) {
            $root["data"] = &$user;
            loadSponsorTreeChildren($pdo, $root, $calculateDownline, $nodesVisited, $max_depth);
        }

        return $root;
    } catch (Exception $e) {
        error_log ($e->getMessage());
        return null;
    }
}


/* loads complete tree from whole user table and calculates downline info; returns desired node */
function loadSponsorTreeByReadingWholeUserTable($pdo, $userId) {
    
    $sql = "SELECT * FROM User WHERE broker_registration_complete=1 ORDER BY id ASC;";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    //$sth->bindParam(':userIdNode', $userIdNode);
    if (!$sth->execute()) {
        return NULL;
    }
    
    $usersFromDb = $sth->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT user_id, SUM(amount) as sum_original, SUM(paid_amount_usd) as sum_paid FROM OpenBuyingTransactions WHERE status='complete' GROUP BY user_id;";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    //$sth->bindParam(':userIdNode', $userIdNode);
    if (!$sth->execute()) {
        return NULL;
    }
    
    $sums = $sth->fetchAll(PDO::FETCH_ASSOC);    

    foreach($sums as $sum)
    {
        $sums_by_user[$sum['user_id']] = $sum;
    }

    //echo "user ".$userId."<br>";
    // echo count($usersFromDb)."<br>";

    $nodes = array();
    foreach ($usersFromDb as &$userFromDb) {
        unset($node);
        $node = array();
        $node["id"] = $userFromDb["id"];
        $node["downline_direct_calculated"] = 0;
        $node["downline_all_calculated"] = 0;
        $node["downline_level_calculated"] = -1;
        $node["children"] = array();
        $node["data"] = &$userFromDb;
        $node["data"]["access_volume_paid"] = 0;
        $node["data"]["access_volume_original"] = 0;
        $nodes[$userFromDb["id"]] = &$node;
    }

    foreach($sums as $sum)
    {
        $nodes[$sum['user_id']]["data"]["access_volume_paid"] = ($sum['sum_paid'] >= 59) ? $sum['sum_paid']-59 : $sum['sum_paid'];
        $nodes[$sum['user_id']]["data"]["access_volume_original"] = ($sum['sum_original'] >= 59) ? $sum['sum_original']-59 : $sum['sum_original'];
    }

    foreach ($usersFromDb as &$userFromDb) {
        $node = &$nodes[$userFromDb["id"]];
        $nodeParent = &$nodes[$userFromDb["upline_user_id"]];
        if($nodeParent) {
            //echo "parent found ".$nodeParent["id"]."<br>";
            $nodeParent["children"][$userFromDb["id"]] = &$node;
        } else {
            // echo "parent not found ".$userFromDb["upline_user_id"]."<br>";
        }
    }

    $nodeRoot = &$nodes[1];
    if($nodeRoot) {
        $nodeRoot["downline_level_calculated"] = 0;
        calculateDownline($nodeRoot);
    }

    $nodeUser = &$nodes[$userId];

    return $nodeUser;
}


function calculateDownline(&$node) {
    if(!$node["downline_level_calculated"])
        $node["downline_level_calculated"] = 0;
    $node["downline_direct_calculated"] = count($node["children"]);
    $node["downline_all_calculated"] = $node["downline_direct_calculated"];
    $node["access_downline_total_calculated"] = $node["data"]["access_volume_paid"];

    foreach ($node["children"] as &$child) {
        $child["downline_level_calculated"] = $node["downline_level_calculated"] + 1;
        calculateDownline($child);
    }

    foreach ($node["children"] as &$child) {
        $node["downline_all_calculated"] += $child["downline_all_calculated"];
        $node["access_downline_total_calculated"] += $child["access_downline_total_calculated"];
        
        $child["access_downline_total_calculated"] -=  $child["data"]["access_volume_paid"];
    }

}

function writeDownlineInfoCalculatedOfTree($pdo, &$node) {
    $sql = "UPDATE User SET downline_direct_count=:dlDirectCount, downline_total_count=:dlTotalCount, downline_level=:dlLevel, ".
                "access_downline_total=:accessDownlineTotal, access_volume_paid=:access_volume_paid, access_volume_original=:access_volume_original WHERE id=:userId;";
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(':dlDirectCount', $node["downline_direct_calculated"]);
    $sth->bindParam(':dlTotalCount', $node["downline_all_calculated"]);
    $sth->bindParam(':dlLevel', $node["downline_level_calculated"]);
    $sth->bindParam(':accessDownlineTotal', $node["access_downline_total_calculated"]);
    $sth->bindParam(':userId', $node["data"]["id"]);
    $sth->bindParam(':access_volume_paid', $node["data"]["access_volume_paid"]);
    $sth->bindParam(':access_volume_original', $node["data"]["access_volume_original"]);
    if (!$sth->execute()) {
        return NULL;
    }
        
    foreach ($node["children"] as &$child) {
        writeDownlineInfoCalculatedOfTree($pdo, $child);
    }
}

function getCountryByIso($pdo, $isoCode, $lang = '')
{
    $sql = "SELECT Countries.* :translateName FROM Countries";
    if(!empty($lang)) {
        $sql .=  " JOIN CountriesTranslation ON CountriesTranslation.isocode = Countries.iso";
    }
    $sql .= " WHERE Countries.iso = :iso AND Countries.active = 1";
    if(!empty($lang)) {
        $sql .= " AND CountriesTranslation.language = :language";

        $sql = str_replace(":translateName", ", CountriesTranslation.language, COALESCE(CountriesTranslation.name, Countries.nicename) as translated_name", $sql);
    } else {
        $sql = str_replace(":translateName", ", Countries.nicename as translated_name", $sql);
    }
    
    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->bindParam(':iso', $isoCode);
    if(!empty($lang)) {
        $sth->bindParam(':language', strtolower($lang));
    }
    
    if (!$sth->execute()) {
        $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
        error_log($msg);
    }
    
    $country = $sth->fetch(PDO::FETCH_ASSOC);
    if (empty($country) && !empty($lang)) //if no result, it could mean there is no translation for selected language -> fallback to call without language
    {
        getCountryByIso($pdo, $isoCode);
    }

    return $country;
}

function getAllCountries($pdo, $language = '') {
    $sql = "SELECT * FROM Countries c";
    $params = array();
    if (!empty($language))
    {
        $sql .= " JOIN CountriesTranslation t ON c.iso = t.isocode AND t.language = :language";
        $params[':language'] = $language;
    }
    $sql .= " WHERE c.active = 1";

    $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    if (!$sth->execute($params)) {
        $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
        error_log($msg);
    }

    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    if (empty($result) && !empty($language)) //if no result, it could mean there is no translation for selected language -> fallback to call without language
    {
        $result = getAllCountries($pdo);
    }

    return $result;
}

function _updateOrders(&$pdo, $userid, $tbl_name, &$orders = [])
{
    if (empty($tbl_name)) {
        return false;
    }
    
    try {
        $pdo->beginTransaction();

        //delete old pending orders
        $sql = "DELETE FROM `$tbl_name` WHERE `userid` = :userid";
        $sth = $pdo->prepare($sql);
        $sth->bindParam(':userid', $userid);
        $sth->execute();

        date_default_timezone_set('Europe/Berlin');

        $allowed_fields = array("Order", "PP", "CP", "Cmd", "Symbol", "Volume", "OpenPrice", "Sl", "Tp", "OpenTime", "Commission", "Profit", "Storage");

        //insert new ones
        foreach ($orders as $order) {

            //prepare fields/values
            $fields = array('userId');
            $values = array($userid);

            foreach ($allowed_fields as $key) {
                if (array_key_exists($key, $order))
                {
                    $fields[] = $key;

                    //special col: "Open Time"
                    if ($key == "OpenTime") {
                        $values[] = date("Y-m-d H:i:s", $order[$key] - date('Z'));
                    } else {
                        $values[] = $order[$key];
                    }
                }
            }

            $fieldlist = "`" . implode('`,`', $fields) . "`";
            $qs = str_repeat("?,", (count($fields) - 1));

            //insert new orders
            $sql = "INSERT INTO $tbl_name($fieldlist) values(${qs}?)";
            $sth = $pdo->prepare($sql);
            $sth->execute($values);
        }

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        //rethrow exception
        throw $e;
    }
}


function updatePendingOrders($pdo, $userid, $pendingOrders = []) {
    return _updateOrders($pdo, $userid, "PendingOrders", $pendingOrders);
}

function updateOpenOrders($pdo, $userid, $openOrders = []) {
    return _updateOrders($pdo, $userid, "OpenOrders", $openOrders);
}

function insertPerformanceFee($pdo, $date, $user_id, $amount)
{
    $sql = "REPLACE INTO PerformanceFee (user_id, `date`, amount) values(:user_id, :date, :amount)";
    $sth = $pdo->prepare($sql);
    $sth->bindParam(':user_id', $user_id);
    $sth->bindParam(':date', $date);
    $sth->bindParam(':amount', $amount);
    $sth->execute();

    $id = $pdo->lastInsertId();

    return $id;
}

function updateOrderHistory($pdo, $userid, $orders = []) {
    
    //optimistic approach - no updates is success
    if (empty($orders)) {
        return true;
    }

    try {
        $pdo->beginTransaction();

        date_default_timezone_set('Europe/Berlin');
      
        $allowed_fields = array("Order", "Cmd", "Symbol", "Volume", "Open Price", "SL", "TP", "Close Price", "Open Time", "Commission", "Profit", "Storage", "Comment");
        //insert new ones
        foreach ($orders as $order) {

            //prepare fields/values
            $fields = array('userId');
            $values = array($userid);

            foreach ($allowed_fields as $key) {
                if (array_key_exists($key, $order))
                {
                    $fields[] = strtolower(str_replace(" ", "_", $key));

                    //special col: "Open Time"
                    if ($key == "Open Time") {
                        $values[] = date("Y-m-d H:i:s", $order[$key] - date('Z'));
                    } else {
                        $values[] = $order[$key];
                    }
                }
            }
           
            $fieldlist = "`" . implode('`,`', $fields) . "`";
            $qs = str_repeat("?,", (count($fields) - 1));

            //due to overlapping order date (end date prev call is begin actual)
            //perform replace (see MySQL REPLACE)
            $sql = "REPLACE INTO OrderHistory($fieldlist) values(${qs}?)";
            $sth = $pdo->prepare($sql);
            $sth->execute($values);
        }

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        //rethrow exception
        throw $e;
    }
}

function readOrderHistory($pdo, $userId) {
    $sql = "SELECT * FROM OrderHistory WHERE userId=:userId AND (`comment` LIKE '%TradeBot%' OR `comment` LIKE '%ProconBot%') ORDER BY open_time DESC;";
    $sth = $pdo->prepare($sql);
    $sth->bindParam(':userId', $userId);
    $sth->execute();

    $orderHistory = $sth->fetchAll(PDO::FETCH_ASSOC);   

    return $orderHistory;
}

function summarizeClosedTradesPL($pdo, $userId) {
    $sql = "SELECT SUM(profit) as closed_trades FROM `OrderHistory` WHERE `userId` = :userId AND (`comment` LIKE '%TradeBot%' OR `comment` LIKE '%ProconBot%')";
    $sth = $pdo->prepare($sql);
    $sth->bindParam(':userId', $userId);
    $sth->execute();
    return $sth->fetch(PDO::FETCH_ASSOC)['closed_trades'];
}

function summarizeDepositWithdrawal($pdo, $userId) {
    $sql = "SELECT SUM(profit) as total_withdraw_deposit FROM `OrderHistory` WHERE `userId` = :userId AND (`comment` LIKE '%deposit%' OR `comment` LIKE '%withdrawal%') AND `comment` NOT LIKE 'performance fee withdrawal';";
    $sth = $pdo->prepare($sql);
    $sth->bindParam(':userId', $userId);
    $sth->execute();
    return $sth->fetch(PDO::FETCH_ASSOC)['total_withdraw_deposit'];
}

function summarizePerformanceFee($pdo, $userId) {
    $sql = "SELECT SUM(profit) as total_withdraw_deposit FROM `OrderHistory` WHERE `userId` = :userId AND (`comment` LIKE 'performance fee withdrawal');";
    $sth = $pdo->prepare($sql);
    $sth->bindParam(':userId', $userId);
    $sth->execute();
    return $sth->fetch(PDO::FETCH_ASSOC)['total_withdraw_deposit'];
}

function getAllVQsOfUser($pdo, $userId) {
    $sql = "SELECT amount, `type`, created_on FROM Commissions WHERE `user_id`=:userId AND currency='VQ'";
    $sth = $pdo->prepare($sql);
    $sth->bindParam(':userId', $userId);
    $sth->execute();
    return $sth->fetchAll(PDO::FETCH_ASSOC);
}

function searchUsers($pdo, $searchString)
{
    $sql = "SELECT id, given_name, sur_name, email, downline_level FROM User WHERE given_name LIKE :searchString OR sur_name LIKE :searchString OR email LIKE :searchString";
    $sth = $pdo->prepare($sql);
    $searchString = "%$searchString%";
    $sth->bindParam(':searchString', $searchString);
    $sth->execute();
    return $sth->fetchAll(PDO::FETCH_ASSOC);
}

function searchUsersAdminDashboard($pdo, $searchString)
{
    $sql = "SELECT u.id, u.given_name, u.sur_name, u.email, u.affiliate_level, u.career_level, ul.given_name AS upline_given_name, ul.sur_name AS upline_sur_name, u.trading_account, u.automation, u.vq_balance, u.downline_level, u.career_level FROM User u LEFT JOIN User ul ON u.upline_user_id=ul.id WHERE u.given_name LIKE :searchString OR u.sur_name LIKE :searchString OR u.email LIKE :searchString ORDER BY u.id";
    $sth = $pdo->prepare($sql);
    $searchString = "%$searchString%";
    $sth->bindParam(':searchString', $searchString);
    $sth->execute();
    $users = $sth->fetchAll(PDO::FETCH_ASSOC);
    return $users;
}

function getSumAccessVolumePaidAllUsers($pdo) {
    $sql = "SELECT SUM(access_volume_paid) AS sum_access_volume_paid FROM User WHERE 1";
    $sth = $pdo->prepare($sql);
    $sth->execute();
    return $sth->fetch(PDO::FETCH_ASSOC)["sum_access_volume_paid"];
}

function getLeaderboard($pdo, $rankStart, $rankEnd) {
    $sql = "SELECT id, given_name, LEFT(sur_name, 1) AS sur_name_0, max_career_level, downline_direct_count, downline_total_count, access_downline_total, profile_picture_name, max_affiliate_level FROM User WHERE broker_registration_complete=1 ORDER BY max_career_level DESC, downline_direct_count DESC, access_downline_total DESC, downline_total_count DESC, max_affiliate_level DESC, sur_name ASC, given_name ASC, email ASC LIMIT :fromUser, :countUsers";
    $sth = $pdo->prepare($sql);
    $fromUser = $rankStart - 1;
    $countUsers = $rankEnd - $rankStart + 1;
    $sth->bindValue(':fromUser', (int) $fromUser, PDO::PARAM_INT);
    $sth->bindValue(':countUsers', (int) $countUsers, PDO::PARAM_INT);
    $sth->execute();
    $users = $sth->fetchAll(PDO::FETCH_ASSOC);

    $rank = $rankStart;
    foreach ($users as &$user) {
        $user["rank"] = $rank;
        $user["imageBase64"] = loadUserImageBase64($user["id"], $user["profile_picture_name"], true);
        $rank += 1;
    }

    return $users;
}

function getLeaderboardOwnPosition($pdo, $userId)
{
    $sql = "SELECT id, given_name, LEFT(sur_name, 1) AS sur_name_0, max_career_level, downline_direct_count, downline_total_count, access_downline_total, profile_picture_name, max_affiliate_level FROM User WHERE broker_registration_complete=1 ORDER BY max_career_level DESC, downline_direct_count DESC, access_downline_total DESC, downline_total_count DESC, max_affiliate_level DESC, sur_name ASC, given_name ASC, email ASC";
    $sth = $pdo->prepare($sql);
    $sth->execute();
    $users = $sth->fetchAll(PDO::FETCH_ASSOC);

    $rank = -1;
    foreach ($users as $rank_ => $user) {
        if ($user["id"] == $userId) {
            $rank = $rank_ + 1;
        }
    }

    $usersOwnPosition = array();

    if ($rank == -1)
        return $usersOwnPosition; // user not found

    $numUsers = count($users);

    $rankStart = $rank - 1;
    $rankEnd = $rank + 1;
    if($rank == 1) {
        $rankStart = 1;
        $rankEnd = 3;
    } else if($rank == $numUsers) {
        $rankStart = $numUsers - 2;
        $rankEnd = $numUsers;
    }

    if($rankStart < 1)
        $rankStart = 1;
    if($rankEnd > $numUsers)
        $rankEnd = $numUsers;

    $ctrRank = $rankStart;
    while($ctrRank <= $rankEnd) {
        $userRanked = &$users[$ctrRank - 1];
        $userRanked["rank"] = $ctrRank;
        $userRanked["imageBase64"] = loadUserImageBase64($userRanked["id"], $userRanked["profile_picture_name"], true);
        $usersOwnPosition[$ctrRank] = $userRanked;
        $ctrRank++;
    }

    return $usersOwnPosition;
}

function loadUserImageBase64($userId, $imageFileName, $useDefault=false) {
    $path = PROFILE_PICTURE_ROOT_DIR . $userId . PICTURE_DIR . $imageFileName; 
    if(!$imageFileName || !file_exists($path)) {
        if(!$useDefault)
            return null;
        $path = "./Images/profile.jpg";
    }
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    return $base64;
}
