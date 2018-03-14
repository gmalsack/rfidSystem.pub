<html>
<body>
<?php

echo "<AVEA>";
// setup variables
$varCmd = $_REQUEST["cmd"]; // get the command from the reader
$varModel = $_REQUEST["mode"]; // get the reader model
$varType = $_REQUEST["type"]; //get the reader type m=master s=slave
$varCode = $_REQUEST["code"]; // get the card code
$varTime = $_REQUEST["time"]; // get the time when card code is logged
$varDate = $_REQUEST["date"]; // get the date when card code is logged
$varDoor = $_REQUEST["deviceid"]; // get the reader id
$varNow = time(); // current time stamp
$varTimeStamp = date('Y-m-d H:i:s',$varNow); // set the datetime string to correct format
$varReaderTime = "$varDate $varTime"; // access the date and time of the reader

$dbl= new SQLite3('/mnt/sqlite/accessLog.db');
$dbr= new SQLite3('/mnt/sqlite/rfid.db');

function logger($msg) {
	$varNow = time(); // current time stamp
	$varTimeStamp = date('Y-m-d H:i:s',$varNow); // set the datetime string to correct format
	$fh = fopen("/var/log/rfidLog","a+");
	$logMsg = "$varTimeStamp : $msg\n";
	fwrite($fh,$logMsg);
	fclose($fh);
}

function report($varKFID,$varEmpID,$sqlDoorID,$varResult) {
	global $dbr,$dbl,$varTimeStamp;
	$sql = "select `name` from `employees` where `id` = '$varEmpID'";
	logger("$sql");
	$result = $dbr->query($sql);
	list($varEmpName) = $result->fetchArray(); 
	$sql = "select `name` from `doors` where `id` = '$sqlDoorID'";
	logger("$sql");
	$result = $dbr->query($sql);
	list($varDoorName) = $result->fetchArray(); 
	$sql = "insert into `accessLog` (`keyFobID`,`empName`,`doorName`,`timestamp`,`result`) values ('$varKFID','$varEmpName','$varDoorName','$varTimeStamp','$varResult')";
	logger("$sql");
	$result = $dbl->query($sql);
}

switch ($varCmd) {

	case "PU": // power up
		echo "CK=$varTimeStamp"; // set clock
		echo "HB=0030"; // set heartbeat to 30 seconds
	break;
	
	case "CO": // card only
		$sql = "select `id`,`active` from `keyFobs` where `number` = '$varCode'";
		logger("$sql");
		$result = $dbr->query($sql); 
		list($sqlKeyFobID,$sqlKeyFobActive) = $result->fetchArray();
		if ($sqlKeyFobActive == "1") {
			// get sql data
			$sql = "select `id` from `doors` where `number` = '$varDoor'";
			logger("$sql");
			$result = $dbr->query($sql);
			list($sqlDoorID) = $result->fetchArray();
			$sql = "select `id`, `empID` from `keyFobs` where `number` = '$varCode'";
			logger("$sql");
			$result = $dbr->query($sql);
			list($varKFID,$varEmpID) = $result->fetchArray();
			$sql = "select active from `employees` where `id` = '$varEmpID'";
			logger("$sql");
			$result = $dbr->query($sql);
			list ($varActive) = $result->fetchArray();
			$sql = "select `tcid` from `accessClass` where `doorID` = '$sqlDoorID' and `empID` = '$varEmpID'";
			logger("$sql");
			$result = $dbr->query($sql);
			list($sqlTCID) = $result->fetchArray();
			$varNow = time();
			$varDOW = date('D',$varNow);
			$varDOW = strtolower($varDOW);
			$sql = "select `start`,`end`,`".$varDOW."` from `timeClass` where `id` = '$sqlTCID'";
			logger("$sql");
			$result = $dbr->query($sql);
			list($varStart,$varEnd,$varDay) = $result->fetchArray();
			logger("$varStart - $varEnd - $varDay");
			
			// validate data and respond
			$varNow = time();
			$varCurrTime = date('H:i',$varNow);
			if (($varCurrTime > $varStart) and ($varCurrTime < $varEnd) and ($varActive == '1') and ($varDay == '1')) {
				logger("KeyFob $varCode Granted Access");
				echo "GRNT=05";
				echo "BEEP=1";
				// grant access to valid rfid key
				report($varKFID,$varEmpID,$sqlDoorID,'GRANT');
			} else {
				logger("KeyFob $varCode Denied Access");
				echo "DENY";
				echo "BEEP=0";
				// deny access to invalid rfid key
				report($varKFID,$varEmpID,$sqlDoorID,'DENY');
			}
	
		} else {
			logger("KeyFob $varCode Not Active In The Database");
			echo "DENY";
			echo "BEEP=0";
			// deny access
			report($varKFID,$varEmpID,$sqlDoorID,'DENY');
		}
	break;
	
	case "HB": // heartbeat
		echo "CK=$varTimeStamp"; // set clock
	break;

}

echo "</AVEA>";

?>
</body>
</html>

