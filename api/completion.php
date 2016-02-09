<?php
	include '../inc/database.php';
	
	$goal = $_POST['goal'];
	
    $pdo->query("UPDATE goal set complete=NOT complete where id='" . $goal . "'");

	$pdo = null;
?>