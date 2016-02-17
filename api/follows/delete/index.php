<?php
	include '../../../inc/all.php';
	
	$me = $_REQUEST['me'];
	$user = $_REQUEST['user'];

	$db->query("DELETE FROM following WHERE username1 = '${me}' and username2 = '${user}';");

	$results = [];
    $results["meta"]["ok"] = true;
    header("Content-Type: application/json");
    echo json_encode($results);
?>