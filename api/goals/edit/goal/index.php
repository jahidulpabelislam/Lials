<?php
	include '../../../../inc/all.php';
	
	$goalID = $_REQUEST['goalID'];
	$goal = $_REQUEST['goal'];

    $query = "UPDATE goal set goal = '${goal}' where id=${goalID};";
    $db->query($query);

    $query = "SELECT * FROM goal WHERE id=${goalID};";
    $goal = $db->query($query);
    $rows = $goal->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    $results["rows"] = $rows;
	header("Content-Type: application/json");
    echo json_encode($results);
?>