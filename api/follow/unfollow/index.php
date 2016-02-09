<?php
	include '../../../inc/all.php';
	
	$username1 = $_POST['username1'];
	$username2 = $_POST['username2'];

	$db->query("DELETE FROM following WHERE username1 = '${username1}' and username2 = '${username2}';");
?>