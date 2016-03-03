<?php
	$option = array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION);
	try {
		$db = new PDO($dsn, $username, $password, $option);
	}
	catch (PDOException $failure) {
		echo 'Connection failed: ' . $failure->getMessage();
	}
	try {
		$showquery = "SHOW DATABASES LIKE '${dbase}'";
		$showresult = $db->query($showquery);
		if (!$showresult->fetch()) {
			$db->query("CREATE DATABASE ${dbase}"); 
		}
		$db->exec("USE ${dbase}");
		$db->exec($createquery);
	}
	catch ( PDOException $failure ) { 
		echo 'Server failed: ' . $failure->getMessage();		       	
	}
?>