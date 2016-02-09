<?php
	include '../../inc/all.php';
	
	$goalid = $_POST['goalID'];
	$username = $_POST['username'];

  	$db->query("INSERT INTO liked (username, goalid) VALUES ('${username}',${goalid});");
?>