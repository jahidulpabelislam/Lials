<?php
	include '../inc/database.php';
	
	$username = "jahidulislam";
	$username2 = $_POST['username'];

	$goals = $pdo->query("SELECT * FROM goal where username = '" . $username2 ."' order by upload");
	echo "<div id='profilediv'><img class='profilepicture' src='images/profile.svg' alt='Profile Picture'><p class='username'>" . $username2 . "</p></div>";
	if ($goals->rowCount() == 0) {	
		echo "<p>They Haven't Got Any Goals.</p>";
	}
	else {
		while ($goal = $goals->fetch(PDO::FETCH_ASSOC)) {
			echo "<div class='goal' id='" . $goal['id'] . "'><p class='goal'>" . $goal['goal'] . " </p><p class='due'>" . $goal['due'] ."</p><p class='username'>" . $goal['username'] . "</p><div class='images'><img class='comment' src='images/comment.svg' alt='Comment'>";
			if ($goal['username'] != $username) {
				$friend = $pdo->query("SELECT * FROM following WHERE username1 = '" . $username . "' and username2 = '" . $goal['username'] . "'");
				if ($friend->rowCount() == 1) {
					echo "<img class='add' src='images/remove.svg' alt='Add'>";
				}
				else {
					echo "<img class='add' src='images/add.svg' alt='Add'>";
				}
			
				$like = $pdo->query("SELECT * FROM liked WHERE username = '" . $username . "' and goalid = '" . $goal['id'] . "'");
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
			$comments = $pdo->query("SELECT * FROM comment WHERE goalID = '" . $goal['id'] . "' order by upload");
			if ($comments->rowCount() == 0) {	
				echo "<div class='comment'><p>No Comment/s</p></div>";
			}
			else {
				while ($comment = $comments->fetch(PDO::FETCH_ASSOC)) { 
					echo "<div class='comment' id='" . $comment['id'] . "'><p>" . $comment['comment'] . "</p><p class='username'>" . $comment['username'] . "</p><div class='images'>";
					if ($comment['username'] != $username) {
						$friend = $pdo->query("SELECT * FROM following WHERE username1 = '" . $username . "' and username2 = '" . $comment['username'] . "'");
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

			}
			echo "</div>";
		}
	}
?>