<?php
	include '../inc/all.php';
	
	$comment = $_POST['comment'];
	
    $pdo->query("DELETE FROM comment where id=" . $comment);

	$pdo = null;
?>