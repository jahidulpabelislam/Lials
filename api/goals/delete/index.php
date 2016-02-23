<?php
	include '../../../inc/all.php';
	
	$goalID = $_REQUEST['goalID'];
	
    $db->query("DELETE FROM comment where goalid = ${goalID}; DELETE FROM liked where goalid = ${goalID}; DELETE FROM goal where id = ${goalID};");
?>