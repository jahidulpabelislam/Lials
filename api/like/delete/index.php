<?php
	include '../../../inc/database.php';
	
	$goalid = $_POST['goalID'];
	$username = $_POST['username'];

	$db->query("DELETE FROM liked WHERE username = '${username}' and goalid = ${goalid};");
?>