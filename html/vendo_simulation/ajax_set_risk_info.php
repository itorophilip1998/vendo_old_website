<?php

    $result=[];

	try {            
        include("configuration.php");
        include("db.php");

		$pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //
        $userId = trim($_REQUEST["userId"]);
        $ownMoney = trim($_REQUEST["ownMoney"]);
        $awarenessTotalLoss = trim($_REQUEST["awarenessTotalLoss"]);
        $assetsVolume = trim($_REQUEST["assetsVolume"]);
        $existenceThreat = trim($_REQUEST["existenceThreat"]);

        //
        $result["message"] = "";

        if(!$userId)
            $result["message"] .= "Parameter userId missing. ";
        if($ownMoney != "0" && $ownMoney != "1")
            $result["message"] .= "Parameter ownMoney missing or faulty value. ";
        if($awarenessTotalLoss != "0" && $awarenessTotalLoss != "1")
            $result["message"] .= "Parameter awarenessTotalLoss missing or faulty value. ";
        if($assetsVolume != "1" && $assetsVolume != "2" && $assetsVolume != "3" && $assetsVolume != "4")
            $result["message"] .= "Parameter assetsVolume missing or faulty value. ";
        if($existenceThreat != "0" && $existenceThreat != "1")
            $result["message"] .= "Parameter existenceThreat missing. ";

        $result["message"] = trim($result["message"]);
        if($result["message"]) {
            $result["code"] = -1;
            $jsonOut=json_encode($result);		
		    die($jsonOut);
        }

        //
        $sql = "UPDATE User SET own_money=:ownMoney, awareness_total_loss=:awarenessTotalLoss, ".
                "assets_volume=:assetsVolume, existence_threat=:existenceThreat ".
                " WHERE id=:userId";
        $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->bindParam(':userId', $userId);
        $sth->bindParam(':ownMoney', $ownMoney);
        $sth->bindParam(':awarenessTotalLoss', $awarenessTotalLoss);
        $sth->bindParam(':assetsVolume', $assetsVolume);
        $sth->bindParam(':existenceThreat', $existenceThreat);
        if (!$sth -> execute()) {
            $msg = "Error: ".$sth -> errorInfo()[2];
            error_log($msg);
            $result["code"] = -12;
            $result["message"] = $msg;
            $jsonOut=json_encode($result);
        }

        $result["message"] = "Ok";
        $result["code"] = 1;
		$jsonOut=json_encode($result);
		die($jsonOut);
		
	} catch (Exception $e) {
        $result["message"] = $e->getMessage();
		$jsonOut=json_encode($result);		
		die($jsonOut);
	}
?>