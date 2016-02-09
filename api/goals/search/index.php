<?php

	include '../../../inc/all.php';

	$username = $_GET['username'];
	$search = $_GET['search'];

	$query = "SELECT * FROM goal left join (select * from following where username1 = '${username}') as gf on goal.username = username2  left join (select goalid from liked where username = '${username}') as gl on id = goalid where goal like '%${search}%' order by upload";
	$goals = $db->query($query);
	$rows = $goals->fetchAll(PDO::FETCH_ASSOC);

	$results = [];
	$results["rows"] = $rows;
	header("Content-Type: application/json");
    echo json_encode($results);
?>