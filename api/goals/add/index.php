<?php
	include '../../../inc/all.php';
	
	$goal = $_POST['goal'];
	$due = $_POST['due'];
	$upload = date("Y/m/d");
	$username = $_POST['username'];
	
	$query = "INSERT INTO goal (goal, due, upload, username) VALUES ('${goal}','${due}','${upload}','${username}');";
    $db->query($query);

    $id = $db->lastInsertId();

    $query = "SELECT * FROM goal left join (select * from following where username1 = '${username}') as gf on goal.username = username2  left join (select goalid from liked where username = '${username}') as gl on id = goalid WHERE id=${id};";
    $goal = $db->query($query);
    $rows = $goal->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    $results["rows"] = $rows;
	header("Content-Type: application/json");
    echo json_encode($results);
?>