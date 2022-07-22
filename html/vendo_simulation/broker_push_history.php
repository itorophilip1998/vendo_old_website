<?php
require_once('configuration.php');
require_once('db.php');
require_once('lib/commissions.php');

$data_raw = file_get_contents("php://input");

$data = json_decode($data_raw);

if ($data->secret === BROKER_SECRET)
{
    $date = $data->date;

    $pdo = getDatabase();
    $commissions_calculator = new Commissions;
    foreach($data->performance_fees as $fee)
    {
        insertPerformanceFee($pdo, $date, $fee->user_id, $fee->fee);

        $commissions_calculator->assignCommissions($fee->user_id, $fee->fee, 'Performance Fee', Commissions::COMMISSION_SOURCE_BROKER);     
    }
    
    die('{"result":"ok"}');
}

die('{"result":"error_auth"}');

