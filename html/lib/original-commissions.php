<?php

require_once("configuration.php");
require_once("db.php");
require_once("utils.php");

class Commissions
{

    protected static $db = null;

    public function recalculateDecisionBonus()
    {
        try {
            $date = date("Y-m-d");
            // calculate decision bonus twice for each user, normal and restart decision bonus            

            //get all users whose decision bonus time is not yet over (flag not set yet)
            $users = $this->getUsersViableForDecisionBonus('normal');

            foreach ($users as $user) {

                $decision = $this->getActualDecisionBonus($date, 'normal');

                if ($decision) {
                    $decision[0]['start'] = $user['decision_start'];

                    $this->calculateDecisionBonus($user['id'], $decision[0]);

                    //set flag decision bonus time over for users who registered more than duration + 7 days ago
                    $this->setDecisionTimedOutFlag($decision[0]['duration']);
                }
            }

            $decision_restart = $this->getActualDecisionBonus($date, 'restart');

            if ($decision_restart) {
                //get all registered users
                $users = $this->getUsersViableForDecisionBonus('restart');

                foreach ($decision_restart as $decision) {
                    foreach ($users as $user) {
                        $this->calculateDecisionBonus($user['id'], $decision);
                    }
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    protected function getActualDecisionBonus($date, $type)
    {
        $pdo = self::getDatabase();

        if ($type === 'normal') {
            $sql = "SELECT * FROM DecisionBonus WHERE `start` <= :date AND `type` = 'normal' ORDER BY `start` DESC LIMIT 1";
        } else if ($type === 'restart') {
            $sql = "SELECT * FROM DecisionBonus WHERE `start` <= :date AND :date < DATE_ADD(`start`, INTERVAL `duration` + 7 DAY) AND `type` = 'restart'";
        } else {
            throw new Exception('Unknown DecisionBonus type');
        }
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':date', $date);

        if (!$sth->execute()) {
            $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
            error_log($msg);
            return false;
        }

        $users = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }

    protected function calculateDecisionBonus($user_id, $decision)
    {

        $revenue = $this->getRevenueSumForDecisionBonus($user_id, $decision['start'], $decision['duration']);

        //calculate bonus
        $decision_bonus = $this->getDecisionBonusFromRevenue($revenue, $decision);

        //check already given bonus
        $prev_bonus = $this->getExistingDecisionBonusForUser($user_id, $decision['id']);

        $additional_bonus = $decision_bonus - $prev_bonus;


        if ($additional_bonus > 0) {
            //increase the bonus in Commissions table                    
            $this->addCommission($user_id, $additional_bonus, self::COMMISSION_TYPE_DECISION_VQ, $decision['id'], 0, 0, 1, 0, '', 0, 0, 0, self::COMMISSION_STATUS_CONFIRMED);

            //increase VQ Balance in User table
            $this->addVQToUser($user_id, $additional_bonus);
        }
    }

    public function confirmCommissions($user_id)
    {
        //change status of all commissions for source_partner = $user_id to 'confirmed'
        $pdo = self::getDatabase();

        $sql = "UPDATE Commissions SET "
            . "`status` = '" . self::COMMISSION_STATUS_CONFIRMED . "'"
            . " WHERE source_partner = :partner AND `status` = '" . self::COMMISSION_STATUS_PENDING . "' AND (`type` = '" . self::COMMISSION_TYPE_AFFILIATE . "' OR `type` = '" . self::COMMISSION_TYPE_AFFILIATE_VQ . "' OR `type` = '" . self::COMMISSION_TYPE_UNSTOPPABLE . "')";
        $sth = $pdo->prepare($sql);

        $sth->bindParam(':partner', $user_id);

        if (!$sth->execute()) {
            error_log("Update Commissions failed!");
            return false;
        }

        return true;
    }

    public function assignCommissions($user_id, $original_amount, $product, $source = self::COMMISSION_SOURCE_ACCESS)
    {
        $pdo = self::getDatabase();
        $pdo->beginTransaction();

        try {

            $commissionable_amount = $this->getCommissionableAmount($original_amount, $source);

            $status = self::COMMISSION_STATUS_CONFIRMED;

            //////////////////////////////////////////////////////////////////
            // Affiliate Bonus

            //go up to four levels up, calculate commission for each level and store it in the database
            $user = $this->getUser($user_id);

            $upline_id = $user['upline_user_id'];

            $level = 1;
            $prev_user_id = $user_id;
            $unstoppable_bonus_assigned = 0;
            $total_applied_affiliate_bonus = 0;
            while ($upline_id > 0 && $level <= 4) {
                $upline_user = $this->getUser($upline_id);
                $affiliate_level = $this->calculateAffiliateLevel($upline_id, $upline_user['max_affiliate_level']);

                $commission_amount = $this->getAffiliateCommisionForLevel($level, $affiliate_level, $upline_user['trading_account'], $commissionable_amount, /*out*/ $applied_percent);

                $total_applied_affiliate_bonus += $commission_amount;

                if ($level == 1) {
                    //this reduces the unstoppable bonus
                    $unstoppable_bonus_assigned = self::COMMISSION_RATES_BY_LEVEL[1];
                }

                if ($commission_amount != 0) {
                    $this->addCommission($upline_id, $commission_amount, Commissions::COMMISSION_TYPE_AFFILIATE, 0, $prev_user_id, $user_id, $level, $affiliate_level, $product, $original_amount, $applied_percent, $commissionable_amount, $status);
                }

                //go to the next level
                $prev_user_id = $upline_id;
                $upline_id = $upline_user['upline_user_id'];
                $level++;
            }

            //////////////////////////////////////////////////////////////////
            // Unstoppable Bonus     
            $upline_id = $user['upline_user_id'];
            $prev_user_id = $user_id;
            $level = 1;
            $total_applied_unstoppable_bonus = 0;
            while ($upline_id > 0 && $level <= 10000) { //max depth = 10000 - sanity check
                $upline_user = $this->getUser($upline_id);
                $affiliate_level = $this->calculateAffiliateLevel($upline_id, $upline_user['max_affiliate_level']);
                $career_level = $this->calculateCareerlevel($upline_id, $upline_user['max_career_level']);

                $this->rankUp($upline_user, $career_level, $affiliate_level);

                $unstoppable_bonus = $this->getUnstoppableRateFromCareerLevel($career_level);
                if ($unstoppable_bonus > $unstoppable_bonus_assigned) {
                    $commission_amount = $this->getUnstoppableCommisionForLevel($career_level, $affiliate_level, $upline_user['trading_account'], $commissionable_amount, $unstoppable_bonus_assigned, /*out*/ $applied_percent);

                    $total_applied_unstoppable_bonus += $commission_amount;

                    if ($commission_amount != 0) {
                        $this->addCommission($upline_id, $commission_amount, Commissions::COMMISSION_TYPE_UNSTOPPABLE, 0, $prev_user_id, $user_id, $level, $career_level, $product, $original_amount, $applied_percent, $commissionable_amount, $status);

                        $unstoppable_bonus_assigned = $unstoppable_bonus;
                    }
                }

                //early stop if top career level reached
                if ($career_level == self::CAREER_LEVEL_CROWNAMBASSADOR) {
                    break;
                }

                //go to the next level
                $prev_user_id = $upline_id;
                $upline_id = $upline_user['upline_user_id'];
                $level++;
            }

            $pool_amount_unstoppable = $commissionable_amount - $total_applied_affiliate_bonus - $total_applied_unstoppable_bonus;

            $this->assignToPools($pool_amount_unstoppable, $commissionable_amount,  $source, $user_id);

            $pdo->commit();
        } catch (Exception $e) {
            error_log($e->getMessage());
            $pdo->rollback();
        }
    }

    public function recalculateRank()
    {
        $pdo = self::getDatabase();
        $users = readAllRegisteredUsers($pdo);
        foreach ($users as $user) {
            $affiliate_level = $this->calculateAffiliateLevel($user['id'], $user['max_affiliate_level']);
            $career_level = $this->calculateCareerlevel($user['id'], $user['max_career_level']);
    
            $this->rankUp($user, $career_level, $affiliate_level); 
        }       
    }

    protected function rankUp($user, $career_level, $affiliate_level)
    {
        $pdo = $this->getDatabase();
        //rank up unstoppable career level    
        if ($user['career_level'] < $career_level && $user['max_career_level'] < $career_level) { //first time user reaches this level
            //assign VQs Limited Bonus
            $vq_limited_bonus = $this->getLimitedVQForlevel($career_level, $affiliate_level, $user['trading_account']);
            if ($vq_limited_bonus > 0) {
                $this->addCommission($user['id'], $vq_limited_bonus, self::COMMISSION_TYPE_LIMITED_VQ, 0, 0, 0, 0, $career_level, 'Limited Bonus', 0, 0, 0, self::COMMISSION_STATUS_CONFIRMED);

                //increase VQ Balance in User table
                $this->addVQToUser($user['id'], $vq_limited_bonus);
            }

            //set new rank in user

            updateUser($pdo, $user['id'], ['career_level' => $career_level, 'max_career_level' => $career_level], false);

            //assign jackpot
            if ($user['id'] != ROOT_USER && $user['id'] != OWNER_USER) //exclude owner and the root from the jackpot calculation - executive decision
            {
                $jackpot_split = $this->getJackpotSplitForLevel($career_level);
                $jackpot_wins = $this->getJackpotWinsForlevel($career_level);
                if ($jackpot_split > count($jackpot_wins)) //if there are still jackpot slots open and this user is not one of the winners
                {
                    $user_is_winner = false;
                    foreach ($jackpot_wins as $win) {
                        if ($win['user_id'] == $user['id']) {
                            $user_is_winner = true;
                        }
                    }
                    if (!$user_is_winner) {
                        //get jackpot sum
                        $pool = $this->getPoolForLevel($career_level);
                        if ($pool['jackpot_balance'] > 0) {
                            $jackpot_amount = $pool['jackpot_balance'];
                        } else {
                            //first time set fix sum for jackpot                    
                            $jackpot_amount = $this->fixPoolJackpot($career_level);
                        }

                        //split jackpot
                        $amount_per_user = $jackpot_amount / $jackpot_split;

                        //add jackpot to user
                        $this->addCommission($user['id'], $amount_per_user, self::COMMISSION_TYPE_JACKPOT, 0, 0, 0, 0, $career_level, 'Jackpot ' . $pool['name'], $jackpot_amount, 1 / $jackpot_split, $jackpot_amount, self::COMMISSION_STATUS_CONFIRMED);

                        //add jackpot to history (negative)
                        $this->addToPool($pool, -$amount_per_user, self::COMMISSION_SOURCE_JACKPOT, $user['id'], false);
                    }
                }
            }

            //activate next pool
            $this->activatePool($career_level + 1);
        }

        //rank up affiliate level
        if ($user['affiliate_level'] < $affiliate_level && $user['max_affiliate_level'] < $affiliate_level) { //first time user reaches this level
            //assign VQs Limited Bonus
            $vq_limited_bonus = $this->getAffiliateVQForLevel($affiliate_level, $user['trading_account']);
            if ($vq_limited_bonus > 0) {
                $this->addCommission($user['id'], $vq_limited_bonus, self::COMMISSION_TYPE_LIMITED_VQ, 0, 0, 0, 0, $affiliate_level, 'Limited Bonus', 0, 0, 0, self::COMMISSION_STATUS_CONFIRMED);

                //increase VQ Balance in User table
                $this->addVQToUser($user['id'], $vq_limited_bonus);
            }

            //set new rank in user            
            updateUser($pdo, $user['id'], ['affiliate_level' => $affiliate_level, 'max_affiliate_level' => $affiliate_level], false);
        }
    }


    protected function assignToPools($amount, $commissionable_amount, $source, $user_id)
    {
        //assign amount for higher pools if started
        $jackpot_pools = $this->getActiveJackpotPools();
        foreach ($jackpot_pools as $pool) {
            $rate = $this->getUnstoppableRateFromCareerLevel($pool['rank']) - $this->getUnstoppableRateFromCareerLevel($pool['rank'] - 1);
            $pool_amount = $commissionable_amount * $rate;
            $amount -= $pool_amount;
            if ($pool_amount > 0) {
                $this->addToPool($pool, $pool_amount, $source, $user_id);
            }
        }

        //assign rest to the lower 4 pools
        $basic_pools = $this->getBasicPools();
        foreach ($basic_pools as $pool) {
            $rate = $pool['rate'];
            $pool_amount = $amount * $rate;
            if ($pool_amount > 0) {
                $this->addToPool($pool, $pool_amount, $source, $user_id);
            }
        }
    }

    protected function getActiveJackpotPools()
    {
        $pdo = self::getDatabase();

        $sql = "SELECT * FROM Pools WHERE started = TRUE AND closed = FALSE AND rank >= " . self::CAREER_LEVEL_GREENDIAMOND;

        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$sth->execute()) {
            $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
            error_log($msg);
        }

        $users = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }

    protected function getBasicPools()
    {
        $pdo = self::getDatabase();

        $sql = "SELECT * FROM Pools WHERE started = TRUE AND rank <= " . self::CAREER_LEVEL_DIAMOND;

        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$sth->execute()) {
            $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
            error_log($msg);
        }

        $users = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }

    protected function addToPool($pool, $amount, $source, $user_id, $add_balance = true)
    {
        $pdo = self::getDatabase();

        $pool_id = $pool['id'];

        $sql = "INSERT INTO PoolHistory (pool_id, amount, source, user_id) VALUES (:pool_id, :amount, :source, :user_id)";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':pool_id', $pool_id);
        $sth->bindParam(':amount', $amount);
        $sth->bindParam(':source', $source);
        $sth->bindParam(':user_id', $user_id);

        if (!$sth->execute()) {
            $msg = "Error: " . $sth->errorInfo()[2];
            error_log($msg);
            throw new Exception($sth->errorInfo()[2], $sth->errorInfo()[1]);
        }

        if ($add_balance) {
            if ($pool['closed']) {
                //distribute the amount among all users im pool
                $users = $this->getUsersInPool($pool['rank']);
                $count_users = count($users);
                $amount_per_user = $amount / $count_users;

                foreach ($users as $user) {
                    $affiliate_level = $this->calculateAffiliateLevel($user['id'], $user['max_affiliate_level'], $user['active_direct_count']);
                    $trading_account = max($affiliate_level, $user['trading_account']); //special rule - members who gain career level, get higher commission - remove this line if this rule gets removed

                    $factor_from_trading_account = $this->getCommissionRateFromTradingAccount($trading_account);
                    $user_amount = $amount_per_user * $factor_from_trading_account;
                    $this->addCommission($user['id'], $user_amount, self::COMMISSION_TYPE_POOL, 0, 0, 0, 0, $pool['rank'], $pool['name'], $amount, (1 / $count_users) * $factor_from_trading_account, $amount, self::COMMISSION_STATUS_CONFIRMED);
                }
            } else {
                //add amount to the pool
                $sql = "UPDATE Pools SET balance = balance + :amount WHERE id = :pool_id";
                $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                $sth->bindParam(':pool_id', $pool_id);
                $sth->bindParam(':amount', $amount);

                if (!$sth->execute()) {
                    $msg = "Error: " . $sth->errorInfo()[2];
                    error_log($msg);
                    throw new Exception($sth->errorInfo()[2], $sth->errorInfo()[1]);
                }
            }
        }
    }

    public function getUsersInPool($career_level)
    {
        //get all users that have given career level and have needed number of new direct members in last month(s)
        //ALGO: 
        // get count of direct members for each user, grouped by time interval in steps of 30 days
        // result looks like this:
        //
        // id   active_direct_count     month_ago
        //  1           1                   1
        //  1           3                   2
        //  2           2                   1
        //  2           5                   2
        //  2           1                   7
        //
        //  for each row
        //      add count to the user with actual id
        //      check if sum / 2 >= actual month_ago
        //  
        //  example for user 1:
        //  add 1
        //  sum / 2 >= 1 (1 / 2 = 0.5 >= 1 -> FALSE)
        //  add 3
        //  sum / 2 >= 2 (4 / 2 = 2 >= 2 -> TRUE)



        $pdo = self::getDatabase();

        $sql = "SELECT u.id, m.active_direct_count, m.month_ago FROM User u JOIN (SELECT COUNT(*) as active_direct_count, upline_user_id, (DATEDIFF(NOW(), date_of_entry) DIV 30 + 1) as month_ago FROM User WHERE broker_registration_complete = 1 AND balance >= :balance GROUP BY upline_user_id, month_ago) m ON u.id = m.upline_user_id WHERE max_career_level = :level ORDER BY u.id, m.month_ago";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':level', $career_level);
        $min_balance = self::MIN_ACTIVE_BALANCE;
        $sth->bindParam(':balance', $min_balance);
        if (!$sth->execute()) {
            $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
            error_log($msg);
        }

        $users_direct_statisticts = $sth->fetchAll(PDO::FETCH_ASSOC);

        $user_direct_count = array();
        $qualified_users = array();

        //check if user has enough direct members for reverse charge (2 in last 30 days OR 4 in last 60 days OR 6 in last 90 days etc.)
        foreach ($users_direct_statisticts as $stats) {
            if (!array_key_exists($stats['id'], $user_direct_count))
            {
                $user_direct_count[$stats['id']] = 0;
            }
            $user_direct_count[$stats['id']] += $stats['active_direct_count'];

            if ($user_direct_count[$stats['id']] / self::REVERSE_MEMBERS_NEEDED_PER_MONTH >= $stats['month_ago'])
            {
                if (array_search($stats['id'], $qualified_users) === false) //not already in the list
                {
                    $qualified_users[] = $stats['id'];
                }
            }
        }

        if (count($qualified_users) > 0)
        {
            $qualified_users_ids = implode(",", $qualified_users);

            $sql = "SELECT u.*, m.active_direct_count FROM User u JOIN (SELECT COUNT(*) as active_direct_count, upline_user_id FROM User WHERE broker_registration_complete = 1 AND balance >= :balance GROUP BY upline_user_id) m ON u.id = m.upline_user_id WHERE id IN ($qualified_users_ids)";

            $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $min_balance = self::MIN_ACTIVE_BALANCE;
            $sth->bindParam(':balance', $min_balance);
            if (!$sth->execute()) {
                $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
                error_log($msg);
            }

            $users = $sth->fetchAll(PDO::FETCH_ASSOC);

            return $users;
        }

        return array();
    }

    protected function getUsersViableForDecisionBonus($type)
    {
        $pdo = self::getDatabase();

        if ($type === 'normal') {
            $sql = "SELECT * FROM User WHERE downline_direct_count > 0 AND decision_bonus_time_over = 0";
        } else if ($type === 'restart') {
            $sql = "SELECT * FROM User WHERE downline_direct_count > 0";
        }
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$sth->execute()) {
            $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
            error_log($msg);
        }

        $users = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }

    protected function getRevenueSumForDecisionBonus($user_id, $decision_start, $decision_duration)
    {
        $pdo = self::getDatabase();

        //todo: after the first wawe on 01.07.2020 change this so that date of entry must be AFTER the decision start and not more than <duration> days after decision start
        $sql = "SELECT SUM(COALESCE(access_volume_original,0)) FROM User WHERE upline_user_id = :user_id AND broker_registration_complete = 1 AND date_of_entry <= DATE_ADD(:start, INTERVAL :duration DAY)";
        //$sql = "SELECT SUM(COALESCE(access_volume_original,0)) FROM User WHERE upline_user_id = :user_id AND broker_registration_complete = 1 AND date_of_entry >= :start AND date_of_entry <= DATE_ADD(:start, INTERVAL :duration DAY)";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':user_id', $user_id);
        $sth->bindParam(':start', $decision_start);
        $sth->bindParam(':duration', $decision_duration);

        if (!$sth->execute()) {
            $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
            error_log($msg);
        }

        $total_revenue = $sth->fetchColumn();

        return $total_revenue;
    }

    protected function getDecisionBonusFromRevenue($revenue, $levels)
    {
        if ($revenue < $levels['threshold1']) //1500
        {
            return 0;
        }

        //from 1500 to 3000
        if ($revenue < $levels['threshold2']) //3000
        {
            return $levels['award1']; //30000
        }

        //from 3000 to 7500
        if ($revenue < $levels['threshold3']) //7500
        {
            return $levels['award2']; //100000
        }

        //from 7500 bonus is 300.000 + another 300.000 for each 2500$ over 7500$
        $vq_total_amount = $levels['award3']; //300000

        $vq_total_amount += floor(($revenue - $levels['threshold3']) / $levels['threshold_n']) * $levels['award_n'];

        return $vq_total_amount;
    }

    protected function getExistingDecisionBonusForUser($user_id, $decision_id)
    {
        //bindParam needs variable
        $type_decision = self::COMMISSION_TYPE_DECISION_VQ;

        $pdo = self::getDatabase();

        $sql = "SELECT SUM(amount) FROM Commissions WHERE user_id = :user_id AND type = :type AND decision_id = :decision_id";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':user_id', $user_id);
        $sth->bindParam(':type', $type_decision);
        $sth->bindParam(':decision_id', $decision_id);

        if (!$sth->execute()) {
            $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
            error_log($msg);
        }

        $total_revenue = $sth->fetchColumn();

        return $total_revenue;
    }

    protected function addVQToUser($user_id, $amount)
    {
        $pdo = self::getDatabase();

        $sql = "UPDATE User SET `vq_balance`= `vq_balance` + :amount WHERE id=:id";
        $sth = $pdo->prepare($sql);
        $sth->bindParam(':id', $user_id);
        $sth->bindParam(':amount', $amount);

        if (!$sth->execute()) {
            $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
            error_log($msg);
        }
    }

    protected function setDecisionTimedOutFlag($duration)
    {
        $pdo = self::getDatabase();

        $sql = "UPDATE User SET `decision_bonus_time_over`= 1 WHERE NOW() > DATE_ADD(decision_start, INTERVAL :duration + 7 DAY)";
        $sth = $pdo->prepare($sql);
        $sth->bindParam(':duration', $duration);

        if (!$sth->execute()) {
            $msg = "Database Error: " . $sth->errorInfo()[2] . PHP_EOL . "Original SQL: $sql";
            error_log($msg);
        }
    }

    protected function calculateAffiliateLevel($user_id, $act_max_level, $direct_count = false)
    {
        if ($direct_count === false) {
            $direct_count = $this->getDirectActiveDownlineCount($user_id);
        }

        if ($direct_count >= 7) {
            return max(Commissions::AFFILIATE_LEVEL_GOLD, $act_max_level);
        }
        if ($direct_count >= 4) {
            return max(Commissions::AFFILIATE_LEVEL_SILVER, $act_max_level);
        }
        if ($direct_count >= 2) {
            return max(Commissions::AFFILIATE_LEVEL_BRONZE, $act_max_level);
        }

        return max(Commissions::AFFILIATE_LEVEL_MEMBER, $act_max_level);
    }

    public static function getNameAffiliateLevel($affiliate_level)
    {
        if ($affiliate_level == Commissions::AFFILIATE_LEVEL_MEMBER)
            return Commissions::AFFILIATE_LEVEL_NAME_MEMBER;
        else if ($affiliate_level == Commissions::AFFILIATE_LEVEL_BRONZE)
            return Commissions::AFFILIATE_LEVEL_NAME_BRONZE;
        else if ($affiliate_level == Commissions::AFFILIATE_LEVEL_SILVER)
            return Commissions::AFFILIATE_LEVEL_NAME_SILVER;
        else if ($affiliate_level == Commissions::AFFILIATE_LEVEL_GOLD)
            return Commissions::AFFILIATE_LEVEL_NAME_GOLD;
        else
            return "";
    }

    public static function getCareerLevelName($career_level)
    {
        if ($career_level == self::CAREER_LEVEL_NONE)
            return self::CAREER_LEVEL_NONE_NAME;
        else if ($career_level == self::CAREER_LEVEL_BLUESAPPHIRE)
            return self::CAREER_LEVEL_BLUESAPPHIRE_NAME;
        else if ($career_level == self::CAREER_LEVEL_REDRUBY)
            return self::CAREER_LEVEL_REDRUBY_NAME;
        else if ($career_level == self::CAREER_LEVEL_GREENEMERALD)
            return self::CAREER_LEVEL_GREENEMERALD_NAME;
        else if ($career_level == self::CAREER_LEVEL_DIAMOND)
            return self::CAREER_LEVEL_DIAMOND_NAME;
        else if ($career_level == self::CAREER_LEVEL_GREENDIAMOND)
            return self::CAREER_LEVEL_GREENDIAMOND_NAME;
        else if ($career_level == self::CAREER_LEVEL_PINKDIAMOND)
            return self::CAREER_LEVEL_PINKDIAMOND_NAME;
        else if ($career_level == self::CAREER_LEVEL_REDDIAMOND)
            return self::CAREER_LEVEL_REDDIAMOND_NAME;
        else if ($career_level == self::CAREER_LEVEL_BLUEDIAMOND)
            return self::CAREER_LEVEL_BLUEDIAMOND_NAME;
        else if ($career_level == self::CAREER_LEVEL_BLACKDIAMOND)
            return self::CAREER_LEVEL_BLACKDIAMOND_NAME;
        else if ($career_level == self::CAREER_LEVEL_CROWNDIAMOND)
            return self::CAREER_LEVEL_CROWNDIAMOND_NAME;
        else if ($career_level == self::CAREER_LEVEL_CROWNPRESIDENT)
            return self::CAREER_LEVEL_CROWNPRESIDENT_NAME;
        else if ($career_level == self::CAREER_LEVEL_CROWNAMBASSADOR)
            return self::CAREER_LEVEL_CROWNAMBASSADOR_NAME;
        else
            return "";
    }

    protected function getCommissionableAmount($original_amount, $source)
    {
        $base_factor = self::COMMISSION_BASE_FACTOR_ACCESS;
        if ($source == self::COMMISSION_SOURCE_BROKER) {
            $base_factor = self::COMMISSION_BASE_FACTOR_BROKER;
        }

        $commissionable_amount = $original_amount * $base_factor;

        return $commissionable_amount;
    }

    protected function getAffiliateCommisionForLevel($level, $affiliate_level, $trading_account, $commissionable_amount, /*out*/ &$applied_percent)
    {
        $trading_account = max($affiliate_level, $trading_account); //special rule - members who gain career level, get higher commission - remove this line if this rule gets removed

        $factor_from_trading_account = $this->getCommissionRateFromTradingAccount($trading_account); //50, 65, 80 or 100%

        $factor_from_level = $this->getCommissionRateFromTreeLevel($level, $affiliate_level);

        $applied_percent = $factor_from_trading_account * $factor_from_level;

        $commission_amount = $commissionable_amount * $applied_percent;

        return $commission_amount;
    }

    protected function getUnstoppableCommisionForLevel($career_level, $affiliate_level, $trading_account, $commissionable_amount, $percent_already_assigned, /*out*/ &$applied_percent)
    {

        $trading_account = max($affiliate_level, $trading_account); //special rule - members who gain career level, get higher commission - remove this line if this rule gets removed

        $factor_from_trading_account = $this->getCommissionRateFromTradingAccount($trading_account); //50, 65, 80 or 100%

        $factor_from_level = $this->getUnstoppableRateFromCareerLevel($career_level) - $percent_already_assigned;

        $applied_percent = $factor_from_trading_account * $factor_from_level;

        $commission_amount = $commissionable_amount * $applied_percent;

        return $commission_amount;
    }

    protected function getAffiliateVQForLevel($affiliate_level, $trading_account)
    {
        $trading_account = max($affiliate_level, $trading_account); //special rule - members who gain career level, get higher commission - remove this line if this rule gets removed

        $factor_from_trading_account = $this->getCommissionRateFromTradingAccount($trading_account); //50, 65, 80 or 100%

        $vq_rate_from_level = $this->getLimitedVQRateFromAffiliateLevel($affiliate_level);

        $vq_amount = $factor_from_trading_account * $vq_rate_from_level;

        return $vq_amount;
    }

    protected function getLimitedVQForLevel($career_level, $affiliate_level, $trading_account)
    {
        $trading_account = max($affiliate_level, $trading_account); //special rule - members who gain career level, get higher commission - remove this line if this rule gets removed

        $factor_from_trading_account = $this->getCommissionRateFromTradingAccount($trading_account); //50, 65, 80 or 100%

        $vq_rate_from_level = $this->getLimitedVQRateFromCareerLevel($career_level);

        $vq_amount = $factor_from_trading_account * $vq_rate_from_level;

        return $vq_amount;
    }

    protected function getCommissionRateFromTradingAccount($trading_account)
    {
        if (array_key_exists($trading_account, self::COMMISSION_RATES_BY_ACCOUNTTYPE)) {
            return self::COMMISSION_RATES_BY_ACCOUNTTYPE[$trading_account];
        }

        return 0;
    }

    protected function getCommissionRateFromTreeLevel($level, $career_level)
    {
        if (array_key_exists($level, self::COMMISSION_RATES_BY_LEVEL)) {
            //by accident is the maximal depth the same value as the carreer level - if it changes, we need to change following code
            if ($career_level >= $level) {
                return self::COMMISSION_RATES_BY_LEVEL[$level];
            } else {
                return 0;
            }
        }

        return 0;
    }

    protected function getUnstoppableRateFromCareerLevel($career_level)
    {
        if (array_key_exists($career_level, self::UNSTOPPABLE_RATES_BY_LEVEL)) {
            return self::UNSTOPPABLE_RATES_BY_LEVEL[$career_level];
        }

        return 0;
    }

    protected function getLimitedVQRateFromAffiliateLevel($affiliate_level)
    {
        if (array_key_exists($affiliate_level, self::VQ_AFFILIATE_RATES_BY_LEVEL)) {
            return self::VQ_AFFILIATE_RATES_BY_LEVEL[$affiliate_level];
        }

        return 0;
    }

    protected function getLimitedVQRateFromCareerLevel($career_level)
    {
        if (array_key_exists($career_level, self::VQ_LIMITED_RATES_BY_LEVEL)) {
            return self::VQ_LIMITED_RATES_BY_LEVEL[$career_level];
        }

        return 0;
    }

    protected function getDirectActiveDownlineCount($user_id)
    {
        $pdo = self::getDatabase();
        $min_balance = self::MIN_ACTIVE_BALANCE;

        $sql = "SELECT COUNT(*) FROM User WHERE upline_user_id = :sponsor_id AND broker_registration_complete = TRUE AND balance >= :balance";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':sponsor_id', $user_id);
        $sth->bindParam(':balance', $min_balance);
        if (!$sth->execute()) {
            $msg = "Error: " . $sth->errorInfo()[2];
            error_log($msg);
            throw new Exception($sth->errorInfo()[2], $sth->errorInfo()[1]);
        }

        $count = $sth->fetchColumn();

        return $count;
    }

    public function getDirectActiveDownlineMembers($user_id)
    {
        $pdo = self::getDatabase();
        $min_balance = self::MIN_ACTIVE_BALANCE;

        $sql = "SELECT * FROM User WHERE upline_user_id = :sponsor_id AND broker_registration_complete = TRUE AND balance >= :balance";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':sponsor_id', $user_id);
        $sth->bindParam(':balance', $min_balance);
        if (!$sth->execute()) {
            $msg = "Error: " . $sth->errorInfo()[2];
            error_log($msg);
            throw new Exception($sth->errorInfo()[2], $sth->errorInfo()[1]);
        }

        $direct_members = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $direct_members;
    }

    public function getTotalActiveDownlineMembers($user_id)
    {
        $pdo = self::getDatabase();
        $min_balance = self::MIN_ACTIVE_BALANCE;

        //recursive Query
        $sql = "select  count(*)
                from    (select * from User
                        where   broker_registration_complete = TRUE
                        and     balance >= :balance
                        order by upline_user_id, id) users_sorted,
                        (select @pv := :top_user_id) initialisation
                where   find_in_set(upline_user_id, @pv)
                and     length(@pv := concat(@pv, ',', id))";

        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':top_user_id', $user_id);
        $sth->bindParam(':balance', $min_balance);
        if (!$sth->execute()) {
            $msg = "Error: " . $sth->errorInfo()[2];
            error_log($msg);
            throw new Exception($sth->errorInfo()[2], $sth->errorInfo()[1]);
        }

        $count = $sth->fetchColumn();

        return $count;
    }

    protected function getCurrencyFromType($type)
    {
        switch ($type) {
            case self::COMMISSION_TYPE_AFFILIATE_VQ:
            case self::COMMISSION_TYPE_DECISION_VQ:
            case self::COMMISSION_TYPE_LIMITED_VQ:
            case self::COMMISSION_TYPE_MANUAL_VQ:
                return self::COMMISSION_CURRENCY_VQ;

            case self::COMMISSION_TYPE_AFFILIATE:
            case self::COMMISSION_TYPE_UNSTOPPABLE:
            case self::COMMISSION_TYPE_JACKPOT:
            case self::COMMISSION_TYPE_POOL:
                return self::COMMISSION_CURRENCY_USD;

            default:
                return '';
        }
    }

    protected function addCommission($user_id, $commission_amount, $type, $decision_id, $direct_partner, $source_partner, $level, $rank, $product, $product_price, $applied_percent, $commissionable_amount, $status = self::COMMISSION_STATUS_PENDING)
    {
        $pdo = self::getDatabase();

        $currency = $this->getCurrencyFromType($type);

        $sql = "INSERT INTO Commissions (user_id, amount, currency, type, decision_id, direct_partner, source_partner, product, product_price, commissionable_amount, applied_percent, depth, `rank`, created_on, status) VALUES (:user_id, :amount, :currency, :type, :decision_id, :direct_partner, :source_partner, :product, :product_price, :commissionable_amount, :applied_percent, :depth, :rank, NOW(), :status)";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':user_id', $user_id);
        $sth->bindParam(':amount', $commission_amount);
        $sth->bindParam(':currency', $currency);
        $sth->bindParam(':type', $type);
        $sth->bindParam(':decision_id', $decision_id);
        $sth->bindParam(':direct_partner', $direct_partner);
        $sth->bindParam(':source_partner', $source_partner);
        $sth->bindParam(':product', $product);
        $sth->bindParam(':product_price', $product_price);
        $sth->bindParam(':commissionable_amount', $commissionable_amount);
        $sth->bindParam(':applied_percent', $applied_percent);
        $sth->bindParam(':depth', $level);
        $sth->bindParam(':rank', $rank);
        $sth->bindParam(':status', $status);
        if (!$sth->execute()) {
            $msg = "Error: " . $sth->errorInfo()[2];
            error_log($msg);
            throw new Exception($sth->errorInfo()[2], $sth->errorInfo()[1]);
        }

        $id = $pdo->lastInsertId();

        return $id;
    }

    public function getStatisticsForUser($userid)
    {
        $pdo = self::getDatabase();

        $sql = "SELECT SUM(amount) as amount, `status` FROM Commissions WHERE `user_id` = :userid AND `type` = '" . self::COMMISSION_TYPE_AFFILIATE . "' GROUP BY `status`";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            ':userid' => $userid
        ])) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return null;
    }

    public function getAllCommissionsForUser($userid)
    {
        $pdo = self::getDatabase();

        $sql = "SELECT Commissions.*, CONCAT(u1.given_name, \" \", UPPER(LEFT(u1.sur_name, 1)), \".\") as `source_partner_name`, CONCAT(u2.given_name, \" \", UPPER(LEFT(u2.sur_name, 1)), \".\") as `direct_partner_name` FROM Commissions";
        $sql .= " LEFT OUTER JOIN User u1 ON u1.id = Commissions.source_partner";
        $sql .= " LEFT OUTER JOIN User u2 ON u2.id = Commissions.direct_partner";
        $sql .= " WHERE Commissions.`user_id` = :userid AND 
                (Commissions.`type` = '" . self::COMMISSION_TYPE_AFFILIATE . "' OR 
                Commissions.`type` = '" . self::COMMISSION_TYPE_UNSTOPPABLE . "' OR 
                Commissions.`type` = '" . self::COMMISSION_TYPE_POOL . "' OR 
                Commissions.`type` = '" . self::COMMISSION_TYPE_JACKPOT . "')
                ORDER BY created_on DESC";

        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            ':userid' => $userid
        ])) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return null;
    }

    public function getBalance($user_id, $currency, $status, $payout_status, /*out*/ &$commission_ids)
    {
        $pdo = self::getDatabase();
        $where = "`user_id` = :userid";
        $params[':userid'] =  $user_id;
        if ($currency !== self::PARAM_IGNORE) {
            $where .= " AND `currency` = :currency";
            $params[':currency'] = $currency;
        }
        if ($status !== self::PARAM_IGNORE) {
            $where .= " AND `status` = :status";
            $params[':status'] = $status;
        }
        if ($payout_status !== self::PARAM_IGNORE) {
            $where .= " AND `paid_out` = :paid_status";
            $params[':paid_status'] = $payout_status;
        }

        $sql = "SELECT SUM(amount) as amount, GROUP_CONCAT(id) as ids FROM Commissions WHERE " . $where;
        $sth = $pdo->prepare($sql);
        if (!$sth->execute($params)) {
            $msg = "Error: " . $sth->errorInfo()[2];
            error_log($msg);
            throw new Exception($sth->errorInfo()[2], $sth->errorInfo()[1]);
        }

        $result = $sth->fetch(PDO::FETCH_ASSOC);

        $commission_ids = $result['ids'];

        return $result['amount'];
    }

    public function setCommissionsPaid($user_id, $commission_ids)
    {
        //change status of all commissions for source_partner = $user_id to 'confirmed'
        $pdo = self::getDatabase();

        $status_paid = self::COMMISSION_PAYOUT_PAID;

        $sql = "UPDATE Commissions SET "
            . "`paid_out` = :status_paid, `paid_out_date` = NOW()"
            . " WHERE user_id = :user_id AND id IN ($commission_ids)";
        $sth = $pdo->prepare($sql);

        $sth->bindParam(':status_paid', $status_paid);
        $sth->bindParam(':user_id', $user_id);

        if (!$sth->execute()) {
            error_log("Update Commissions failed!");
            return false;
        }

        return true;
    }

    public function addVQManual($user_id, $vq_amount)
    {
        $this->addCommission($user_id, $vq_amount, Commissions::COMMISSION_TYPE_MANUAL_VQ, 0, 0, 0, 0, 0, 'Manual Bonus', 0, 0, 0, Commissions::COMMISSION_STATUS_CONFIRMED);
        $this->addVQToUser($user_id, $vq_amount);
    }

    protected function getJackpotWinsForlevel($rank)
    {
        $pdo = self::getDatabase();

        $sql = "SELECT user_id FROM Pools p JOIN PoolHistory h ON h.pool_id = p.id WHERE p.rank = :rank AND h.source = '" . self::COMMISSION_SOURCE_JACKPOT . "'";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([':rank' => $rank])) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return null;
    }

    protected function getPoolForLevel($rank)
    {
        $pdo = self::getDatabase();

        $sql = "SELECT * FROM Pools WHERE rank = :rank";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([':rank' => $rank])) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    protected function fixPoolJackpot($rank)
    {
        $pdo = self::getDatabase();

        $sql = "UPDATE Pools SET jackpot_balance = balance, balance = 0, closed = TRUE WHERE rank = :rank";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':rank', $rank);

        if (!$sth->execute()) {
            $msg = "Error: " . $sth->errorInfo()[2];
            error_log($msg);
            throw new Exception($sth->errorInfo()[2], $sth->errorInfo()[1]);
        }

        $sql = "SELECT jackpot_balance FROM Pools WHERE rank = :rank";
        $sth = $pdo->prepare($sql);
        $sth->bindParam(':rank', $rank);
        if (!$sth->execute()) {
            $msg = "Error: " . $sth->errorInfo()[2];
            error_log($msg);
            throw new Exception($sth->errorInfo()[2], $sth->errorInfo()[1]);
        }

        return $sth->fetchColumn();
    }

    protected function activatePool($rank)
    {
        $pdo = self::getDatabase();

        $sql = "UPDATE Pools SET started = TRUE WHERE rank = :rank";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':rank', $rank);

        if (!$sth->execute()) {
            $msg = "Error: " . $sth->errorInfo()[2];
            error_log($msg);
            throw new Exception($sth->errorInfo()[2], $sth->errorInfo()[1]);
        }
    }

    protected function getUser($user_id)
    {
        $pdo = self::getDatabase();

        return readUser($pdo, $user_id);
    }



    protected static function getDatabase()
    {
        if (!self::$db) {
            self::$db = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$db;
    }

    public static function setDatabase($db_instance)
    {
        self::$db = $db_instance;
    }

    //Ranked Career
    public function calculateCareerlevel($user_id, $act_max_level)
    {
        //get count of direct members
        $direct_members = $this->getDirectActiveDownlineMembers($user_id);

        if (is_array($direct_members)) {

            foreach ($direct_members as &$member) {
                $member['total_downline_active'] = $this->getTotalActiveDownlineMembers($member['id']) + 1; // +1 for direct member himself
            }
            unset($member);

            //go backwards (highest to lowest rank)
            for ($rank = self::CAREER_LEVEL_CROWNAMBASSADOR; $rank > self::CAREER_LEVEL_NONE; $rank--) {
                //claue = what percentage of downline counts for rank calculation
                $clause = $this->getClausePercentageForRank($rank);

                $total_count = 0;
                $members_needed = $this->membersNeededForRank($rank);
                $max_members_from_team = floor($members_needed * $clause);
                foreach ($direct_members as $member) {
                    $total_count += min($max_members_from_team, $member['total_downline_active']);
                }

                if ($total_count >= $this->membersNeededForRank($rank)) {
                    return max($rank, $act_max_level);
                }
            }
        }

        return max(self::CAREER_LEVEL_NONE, $act_max_level);
    }

    protected function getClausePercentageForRank($rank)
    {
        switch ($rank) {
            case self::CAREER_LEVEL_BLUESAPPHIRE:
            case self::CAREER_LEVEL_REDRUBY:
            case self::CAREER_LEVEL_GREENEMERALD:
                return 0.25; //25 %
                break;
            case self::CAREER_LEVEL_DIAMOND:
            case self::CAREER_LEVEL_GREENDIAMOND:
            case self::CAREER_LEVEL_PINKDIAMOND:
            case self::CAREER_LEVEL_REDDIAMOND:
            case self::CAREER_LEVEL_BLUEDIAMOND:
            case self::CAREER_LEVEL_BLACKDIAMOND:
                return 0.20; //20 %
                break;
            case self::CAREER_LEVEL_CROWNDIAMOND:
            case self::CAREER_LEVEL_CROWNPRESIDENT:
            case self::CAREER_LEVEL_CROWNAMBASSADOR:
                return 0.15; //15 %
                break;
            default:
                return 0;
                break;
        }
    }

    protected function membersNeededForRank($rank)
    {
        switch ($rank) {
            case self::CAREER_LEVEL_NONE:
                return 0;
            case self::CAREER_LEVEL_BLUESAPPHIRE:
                return 12;
            case self::CAREER_LEVEL_REDRUBY:
                return 32;
            case self::CAREER_LEVEL_GREENEMERALD:
                return 60;
            case self::CAREER_LEVEL_DIAMOND:
                return 100;
            case self::CAREER_LEVEL_GREENDIAMOND:
                return 300;
            case self::CAREER_LEVEL_PINKDIAMOND:
                return 1000;
            case self::CAREER_LEVEL_REDDIAMOND:
                return 5000;
            case self::CAREER_LEVEL_BLUEDIAMOND:
                return 10000;
            case self::CAREER_LEVEL_BLACKDIAMOND:
                return 20000;
            case self::CAREER_LEVEL_CROWNDIAMOND:
                return 40000;
            case self::CAREER_LEVEL_CROWNPRESIDENT:
                return 70000;
            case self::CAREER_LEVEL_CROWNAMBASSADOR:
                return 100000;
            default:
                return PHP_INT_MAX;
        }
    }

    protected function getJackpotSplitForLevel($rank)
    {
        switch ($rank) {
            case self::CAREER_LEVEL_BLUESAPPHIRE:
            case self::CAREER_LEVEL_REDRUBY:
            case self::CAREER_LEVEL_GREENEMERALD:
                return 1;
            case self::CAREER_LEVEL_DIAMOND:
                return 2;
            case self::CAREER_LEVEL_GREENDIAMOND:
            case self::CAREER_LEVEL_PINKDIAMOND:
            case self::CAREER_LEVEL_REDDIAMOND:
            case self::CAREER_LEVEL_BLUEDIAMOND:
            case self::CAREER_LEVEL_BLACKDIAMOND:
            case self::CAREER_LEVEL_CROWNDIAMOND:
            case self::CAREER_LEVEL_CROWNPRESIDENT:
            case self::CAREER_LEVEL_CROWNAMBASSADOR:
            default:
                return 4;
        }
    }

    //levels
    const AFFILIATE_LEVEL_MEMBER = 1;
    const AFFILIATE_LEVEL_BRONZE = 2;
    const AFFILIATE_LEVEL_SILVER = 3;
    const AFFILIATE_LEVEL_GOLD = 4;

    const AFFILIATE_LEVEL_NAME_MEMBER = "Member";
    const AFFILIATE_LEVEL_NAME_BRONZE = "Bronze";
    const AFFILIATE_LEVEL_NAME_SILVER = "Silver";
    const AFFILIATE_LEVEL_NAME_GOLD = "Gold";

    const CAREER_LEVEL_NONE = 0;
    const CAREER_LEVEL_BLUESAPPHIRE = 1;
    const CAREER_LEVEL_REDRUBY = 2;
    const CAREER_LEVEL_GREENEMERALD = 3;
    const CAREER_LEVEL_DIAMOND = 4;
    const CAREER_LEVEL_GREENDIAMOND = 5;
    const CAREER_LEVEL_PINKDIAMOND = 6;
    const CAREER_LEVEL_REDDIAMOND = 7;
    const CAREER_LEVEL_BLUEDIAMOND = 8;
    const CAREER_LEVEL_BLACKDIAMOND = 9;
    const CAREER_LEVEL_CROWNDIAMOND = 10;
    const CAREER_LEVEL_CROWNPRESIDENT = 11;
    const CAREER_LEVEL_CROWNAMBASSADOR = 12;

    const CAREER_LEVEL_NONE_NAME = "";
    const CAREER_LEVEL_BLUESAPPHIRE_NAME = "Blue Sapphire";
    const CAREER_LEVEL_REDRUBY_NAME = "Red Ruby";
    const CAREER_LEVEL_GREENEMERALD_NAME = "Green Emerald";
    const CAREER_LEVEL_DIAMOND_NAME = "Diamond";
    const CAREER_LEVEL_GREENDIAMOND_NAME = "Green Diamond";
    const CAREER_LEVEL_PINKDIAMOND_NAME = "Pink Diamond";
    const CAREER_LEVEL_REDDIAMOND_NAME = "Red Diamond";
    const CAREER_LEVEL_BLUEDIAMOND_NAME = "Blue Diamond";
    const CAREER_LEVEL_BLACKDIAMOND_NAME = "Black Diamond";
    const CAREER_LEVEL_CROWNDIAMOND_NAME = "Crown Diamond";
    const CAREER_LEVEL_CROWNPRESIDENT_NAME = "Crown President";
    const CAREER_LEVEL_CROWNAMBASSADOR_NAME = "Crown Ambassador";

    //minimum balance for active user
    const MIN_ACTIVE_BALANCE = 250;

    //number of new direct users needed per month to qualify for reverse bonus
    const REVERSE_MEMBERS_NEEDED_PER_MONTH = 0;

    const COMMISSION_BASE_FACTOR_ACCESS = 0.6; //60% of the original amount goes to commission
    const COMMISSION_BASE_FACTOR_BROKER = 0.5; //50% of the performance fee goes to commission

    const COMMISSION_RATES_BY_ACCOUNTTYPE = array(
        1 => 0.5,   //50% (Basic)
        2 => 0.65,  //65% (Plus)
        3 => 0.8,   //80% (Pro)
        4 => 1.0    //100% (Pro+)
    );

    const COMMISSION_RATES_BY_LEVEL = array(
        1 => 0.15,
        2 => 0.05,
        3 => 0.04,
        4 => 0.04
    );

    const VQ_AFFILIATE_RATES_BY_LEVEL = array(
        1 => 0,
        2 => 10000,
        3 => 30000,
        4 => 50000
    );

    const VQ_LIMITED_RATES_BY_LEVEL = array(
        1 => 100000,
        2 => 400000,
        3 => 1000000,
        4 => 2000000,
        5 => 5000000,
        6 => 10000000,
        7 => 25000000,
        8 => 50000000,
        9 => 100000000,
        10 => 200000000,
        11 => 400000000,
        12 => 1000000000
    );

    const UNSTOPPABLE_RATES_BY_LEVEL = array(
        self::CAREER_LEVEL_NONE => 0,
        self::CAREER_LEVEL_BLUESAPPHIRE => 0.30,
        self::CAREER_LEVEL_REDRUBY => 0.43,
        self::CAREER_LEVEL_GREENEMERALD => 0.54,
        self::CAREER_LEVEL_DIAMOND => 0.61,
        self::CAREER_LEVEL_GREENDIAMOND => 0.67,
        self::CAREER_LEVEL_PINKDIAMOND => 0.72,
        self::CAREER_LEVEL_REDDIAMOND => 0.76,
        self::CAREER_LEVEL_BLUEDIAMOND => 0.79,
        self::CAREER_LEVEL_BLACKDIAMOND => 0.81,
        self::CAREER_LEVEL_CROWNDIAMOND => 0.83,
        self::CAREER_LEVEL_CROWNPRESIDENT => 0.85,
        self::CAREER_LEVEL_CROWNAMBASSADOR => 0.87
    );

    //commission sources
    const COMMISSION_SOURCE_ACCESS = 'access';
    const COMMISSION_SOURCE_UPGRADE = 'upgrade';
    const COMMISSION_SOURCE_BROKER = 'broker';
    const COMMISSION_SOURCE_JACKPOT = 'jackpot';

    //commission types
    const COMMISSION_TYPE_AFFILIATE = 'affiliate';
    const COMMISSION_TYPE_AFFILIATE_VQ = 'affiliate_vq';
    const COMMISSION_TYPE_DECISION_VQ = 'decision_vq';
    const COMMISSION_TYPE_UNSTOPPABLE = 'unstoppable';
    const COMMISSION_TYPE_LIMITED_VQ = 'limited_vq';
    const COMMISSION_TYPE_JACKPOT = 'jackpot';
    const COMMISSION_TYPE_POOL = 'pool';
    const COMMISSION_TYPE_MANUAL_VQ = 'manual_vq';

    //commission currencies
    const COMMISSION_CURRENCY_USD = 'USD';
    const COMMISSION_CURRENCY_VQ = 'VQ';

    //commission status type
    const COMMISSION_STATUS_PENDING = 'pending';
    const COMMISSION_STATUS_CONFIRMED = 'confirmed';

    const COMMISSION_PAYOUT_OPEN = 0;
    const COMMISSION_PAYOUT_PAID = 1;

    const PARAM_IGNORE = false;

    const DECISION_BONUS_NORMAL_DURATION = 14; //14 days
}
