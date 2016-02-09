<?php
	include '../inc/all.php';
	
	$goal = $_POST['goal'];
	
    $pdo->query("DELETE FROM goal where id=" . $goal . "; DELETE FROM comment where goalid =" . $goal);

	$pdo = null;
?>