<?php

	include '../../inc/all.php';

	$username = $_GET['username'];

	$query = "SELECT * FROM goal left join (select * from following where username1 = '${username}') as gf on goal.username = username2  left join (select goalid from liked where username = '${username}') as gl on id = goalid order by upload";
	$goals = $db->query($query);
	$rows = $goals->fetchAll(PDO::FETCH_ASSOC);
	/* if ($goals->rowCount() == 0) {	
		echo "<p>No Goals to view, Upload your goals or follow some people.</p>";
	}
	else {
		while ($goal = $goals->fetch(PDO::FETCH_ASSOC)) {
			echo "<div class='goal' id='" . $goal['id'] . "'><p class='goal'>" . $goal['goal'] . " </p><p class='due'>" . $goal['due'] ."</p><p class='username'>" . $goal['username'] . "</p><div class='images'><img class='comment' src='images/comment.svg' alt='Comment'>";
			if ($goal['username'] != $username) {
				$friend = $db->query("SELECT * FROM following WHERE username1 = '" . $username . "' and username2 = '" . $goal['username'] . "'");
				if ($friend->rowCount() == 1) {
					echo "<img class='add' src='images/remove.svg' alt='Add'>";
				}
				else {
					echo "<img class='add' src='images/add.svg' alt='Add'>";
				}			
				$like = $db->query("SELECT * FROM liked WHERE username = '" . $username . "' and goalid = '" . $goal['id'] . "'");
				if ($like->rowCount() == 0) {
					echo "<img class='like' src='images/unlike.svg' alt='Like'>";
				}
				else {
					echo "<img class='like' src='images/like.svg' alt='Like'>";
				}
			}
			else {
				if ($goal['complete'] == true) {
					echo "<img class='complete' src='images/complete.svg' alt='Done'>";
				}
				else {
					echo "<img class='complete' src='images/notcomplete.svg' alt='Done'>";
				}
				echo "<img class='editGoal' src='images/edit.svg' alt='Edit'>
				<img class='deleteGoal' src='images/delete.svg' alt='Delete'>";
			}
			echo "</div><p class='upload'>" . $goal['upload'] . "</p>";
			comments
			echo "</div>";
		} 
	}*/
	$results = [];
	$results["rows"] = $rows;
	header("Content-Type: application/json");
    echo json_encode($results);
?>