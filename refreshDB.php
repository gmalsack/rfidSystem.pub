#!/usr/bin/php
<?php

// setup variables
$dbHost="";
$dbUser = "";
$dbPass = "";
$dbName = "";

$dbl= new SQLite3('/mnt/sqlite/rfid.db');
$dbh = mysqli_connect($dbHost,$dbUser,$dbPass,$dbName);

$sql = "select * from version";
$query = $dbl->query($sql);
while($row = $query->fetchArray()) {
	$sqlliteVersion = $row['version'];
}

$sql = "select * from version";
$result = mysqli_query($dbh,$sql);
list($mySQLVersion) = mysqli_fetch_array($result);

$dbl->close();
mysqli_close($dbh);


if("$mySQLVersion" == "$sqlliteVersion") {
	echo "Versions are the same\n";
	$cmd = "/usr/local/bin/flushLog.php";
	system($cmd);
} elseif("$mySQLVersion" < "1" ) {
	echo "Unable to connect to Primary SQL\n";
	$cmd = "/usr/local/bin/flushLog.php";
	system($cmd);
} else {
	echo "Update Required\n";
	$flushLog = system("/usr/local/bin/flushLog.php");
	if("$flushLog" > "0") {
		echo "Data In Log\n";
		$flushLog = system("/usr/local/bin/flushLog.php");
		if("$flushLog" > "0") {
			// Data Still In Log - Abandon Rebuild
		} else {
			// No More Data In Log - Rebuild Now
			$cmd = "umount /mnt/sqlite";
			system($cmd);
			$cmd = "/usr/local/bin/loadDB.sh";
			system($cmd);
		}
	} else {
		// No Data In Log - Rebuild Now
		$cmd = "umount /mnt/sqlite";
		system($cmd);
		$cmd = "/usr/local/bin/loadDB.sh";
		system($cmd);
	}
}
?>
