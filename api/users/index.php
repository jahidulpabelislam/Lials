<?php
	include '../../inc/all.php';
	
	$username = $_GET['username'];
	$password = $_GET['password'];

	$results = [];

    $query = "SELECT * FROM user WHERE username = '${username}' and password = '${password}';";
    $user = $db->query($query);
    
    if ($user->rowCount() == 0) {	
		$query = "SELECT * FROM user WHERE username = '${username}';";
	    $row = $db->query($query);
	    if ($row->rowCount() == 0) {
	    	$results['meta']['feedback'] = "No user found with that username.";
	    }
	    else {
	    	$results['meta']['feedback'] = "Password is wrong please try again.";
	    }
	}

	$user = $user->fetchAll(PDO::FETCH_ASSOC);

    $results["rows"] = $user;
	header("Content-Type: application/json");
    echo json_encode($results);
?>