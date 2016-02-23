<?php
	include '../../../inc/all.php';
	
	$commentID = $_REQUEST['commentID'];
	$comment = $_REQUEST['comment'];
	
	$query = "UPDATE comment set comment='${comment}' where id=${commentID};";
    $db->query($query);

    $query = "SELECT * FROM comment WHERE id = ${commentID};";
	$comments = $db->query($query);
	$rows = $comments->fetchAll(PDO::FETCH_ASSOC);

	$results = [];
	$results["id"] = $commentID;
	$results["query"] = $query;
	$results["rows"] = $rows;
	header("Content-Type: application/json");
    echo json_encode($results);
?>