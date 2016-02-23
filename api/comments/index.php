<?php
	include '../../inc/all.php';

	$username = $_GET['username'];
	$goalID = $_GET['goalID'];
	
	$query = "SELECT * FROM comment left join (select * from following where username1 = '${username}') as gf on comment.username = username2 WHERE goalID = '${goalID}' order by upload, id";
	$comments = $db->query($query);
	$rows = $comments->fetchAll(PDO::FETCH_ASSOC);
	/* $comments = $db->query("SELECT * FROM comment WHERE goalID = '" . $goal['id'] . "' order by upload");
	if ($comments->rowCount() == 0) {	
		echo "<div class='acomment'><p>No Comment/s</p></div>";
	}
	else {
		while ($comment = $comments->fetch(PDO::FETCH_ASSOC)) { 
			echo "<div class='acomment' id='" . $comment['id'] . "'><p>" . $comment['comment'] . "</p><p class='username'>" . $comment['username'] . "</p><div class='images'>";
			if ($comment['username'] != $username) {
				$friend = $db->query("SELECT * FROM following WHERE username1 = '" . $username . "' and username2 = '" . $comment['username'] . "'");
				if ($friend->rowCount() == 1) {
					echo "<img class='add' src='images/remove.svg' alt='Add'>";
				}
				else {
					echo "<img class='add' src='images/add.svg' alt='Add'>";
				}
			}
			else {
				echo "<img class='editComment' src='images/edit.svg' alt='Edit'><img class='deleteComment' src='images/delete.svg' alt='Delete'>";
			}
			echo "</div><p class='upload'>" . $comment['upload'] . "</p></div>";
		}
	} }*/
	$results = [];
	$results["rows"] = $rows;
	header("Content-Type: application/json");
    echo json_encode($results);
?>