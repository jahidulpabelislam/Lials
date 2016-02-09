<?php
	include '../../../inc/all.php';
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	$query = "INSERT INTO user (username, password) VALUES ('${username}','${password}')";
    $db->query($query);

    $id = $db->lastInsertId();

    $query = "SELECT * FROM user WHERE username = '${username}';";
    $user = $db->query($query);
    $row = $user->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    $results["rows"] = $row;
	header("Content-Type: application/json");
    echo json_encode($results);
?>