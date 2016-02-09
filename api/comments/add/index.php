<?php
	include '../../../inc/all.php';
	
	$goalid = $_POST['goalID'];
	$comment = $_POST['comment'];
	$username = $_POST['username'];
	$upload = date("Y/m/d");

	$query = "INSERT INTO comment (comment, goalid, username, upload) VALUES ('${comment}',${goalid},'${username}','${upload}')";
    $db->query($query);

    $id = $db->lastInsertId();

    $query = "SELECT * FROM comment left join (select * from following where username1 = '${username}') as gf on comment.username = username2 WHERE id=${id};";
    $comment = $db->query($query);
    $row = $comment->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    $results["rows"] = $row;
	header("Content-Type: application/json");
    echo json_encode($results);
?>