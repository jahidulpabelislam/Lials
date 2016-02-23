<?php
	include '../../../../inc/all.php';
	
	$goalID = $_POST['goalID'];
	
    $db->query("UPDATE goal set complete=NOT complete where id=${goalID};");
	
	$query = "SELECT * FROM goal where id=${goalID};";
	$goal = $db->query($query);
	$rows = $goal->fetchAll(PDO::FETCH_ASSOC);
	
	$results = [];
	$results["rows"] = $rows;
	header("Content-Type: application/json");
    echo json_encode($results);
?>