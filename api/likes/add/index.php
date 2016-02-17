<?php
	include '../../../inc/all.php';
	
	$goalID = $_REQUEST['goalID'];
	$me = $_REQUEST['me'];

  	$query = "INSERT INTO liked (username, goalid) VALUES ('${me}', ${goalID});";
  	$db->query($query);

    $query = "SELECT * FROM liked WHERE username='${me}' and goalid = ${goalID};";
    $like = $db->query($query);
    $row = $like->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    $results["rows"] = $row;
    header("Content-Type: application/json");
    echo json_encode($results);
?>