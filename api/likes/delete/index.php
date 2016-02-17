<?php
	include '../../../inc/all.php';
	
	$goalID = $_REQUEST['goalID'];
	$me = $_REQUEST['me'];

	$row = $db->query("DELETE FROM liked WHERE username = '${me}' and goalid = ${goalID};");

	$results = [];
    $results["goalID"] = $goalID;
    $results["row"] = $row;
    header("Content-Type: application/json");
    echo json_encode($results);
?>