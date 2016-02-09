<?php
	include '../inc/database.php';
	
	$comment = $_POST['comment'];
	$newcomment = $_POST['newcomment'];
	
    $pdo->query("UPDATE comment set comment='" . $newcomment . "' where id=" . $comment);
 
	$pdo = null;
?>