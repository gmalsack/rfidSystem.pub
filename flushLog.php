#!/usr/bin/php
<?php

// setup variables
$dbHost = "";
$dbUser = "";
$dbPass = "";
$dbName = "";

$dbl= new SQLite3('/mnt/sqlite/accessLog.db');
$dbh = mysqli_connect($dbHost,$dbUser,$dbPass,$dbName);

$records = '0';

function logger($msg) {
	$varNow = time(); // current time stamp
	$varTimeStamp = date('Y-m-d H:i:s',$varNow); // set the datetime string to correct format
	$fh = fopen("/var/log/rfidLog","a+");
	$logMsg = "$varTimeStamp : $msg\n";
	fwrite($fh,$logMsg);
	fclose($fh);
}


$sql = "select * from accessLog";
$query = $dbl->query($sql);
while($row = $query->fetchArray()) {
	$id = $row['id'];
	$keyFobID = $row['keyFobID'];
	$empName = $row['empName'];
	$doorName = $row['doorName'];
	$timestamp = $row['timestamp'];
	$result = $row['result'];
	$mySQL = "insert into accessLog (`keyFobID`,`empName`,`doorName`,`timestamp`,`result`) values ('$keyFobID','$empName','$doorName','$timestamp','$result')";
	if(mysqli_query($dbh,$mySQL)) {
		$liteSQL = "delete from accessLog where `id` = '$id'";
		$dbl->query($liteSQL);
		$msg = "access record $id moved";
		logger("$msg");
	} else {
		$msg = "unable to access mysql database server to dump access log";
		logger("$msg");
	}
	$records++;
}
echo $records;
?>
