<?php
	include '../../../inc/all.php';
	
	$me = $_REQUEST['me'];
	$user = $_REQUEST['user'];

	$db->query("INSERT INTO following (username1, username2) VALUES ('${me}', '${user}');");

	$query = "SELECT * FROM following WHERE username1='${me}' and username2 = '${user}';";
    $following = $db->query($query);
    $row = $following->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    $results["rows"] = $row;
    header("Content-Type: application/json");
    echo json_encode($results);
?>