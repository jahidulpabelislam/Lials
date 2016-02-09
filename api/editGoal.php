<?php
	include '../inc/database.php';
	
	$goal = $_POST['goal'];
	$newgoal = $_POST['newgoal'];
	
    $pdo->query("UPDATE goal set goal='" . $newgoal . "' where id='" . $goal . "'");
 
	$pdo = null;
?>