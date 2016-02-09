<?php
	include '../../inc/all.php';
	
	$username1 = $_POST['username1'];
	$username2 = $_POST['username2'];

	$db->query("INSERT INTO following (username1, username2) VALUES ('${username1}', '${username2}');");
?>