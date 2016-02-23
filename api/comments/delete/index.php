<?php
	include '../../../inc/all.php';
	
	$commentID = $_REQUEST['commentID'];
	
    $db->query("DELETE FROM comment where id=${commentID};");
?>