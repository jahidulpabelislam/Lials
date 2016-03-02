<?php
	include '../../inc/all.php';
	
	//get the method verb
	$verb = $_SERVER['REQUEST_METHOD'];

	switch ($verb) {
        case "POST":
        	$results = [];
			try {
				$goalID = $_REQUEST['goalID'];
				$me = $_REQUEST['me'];

				$query = "INSERT INTO Liked (Username, goalID) VALUES ('${me}', ${goalID});";
				$row = $db->query($query);

				if (count($row) > 0) {
			        $results['meta']["ok"] = true;
			        $results['meta']["status"] = 201;
			        $results['meta']["msg"] = "Created";
			    }

				$query = "SELECT * FROM Liked WHERE Username = '${me}' AND goalID = ${goalID};";
				$like = $db->query($query);
				$row = $like->fetchAll(PDO::FETCH_ASSOC);
				    
				$results["rows"] = $row;
			}
			catch ( PDOException $failure ) { 
                $results["meta"]["ok"] = false;
                $results["meta"]["exception"] = $failure;               
            }

			header("Content-Type: application/json");
			echo json_encode($results);

		    break;
		case "DELETE":
			$results = [];
			try {
				$goalID = $_REQUEST['goalID'];
				$me = $_REQUEST['me'];

				$results['meta']["action"] = "delete";

				$query = "DELETE FROM Liked WHERE Username = '${me}' AND GoalID = ${goalID};";
				$row = $db->query($query);

				$results["meta"]["ok"] = (count($row->rowCount()) > 0);

			    $results["rows"]["goalID"] = $goalID;
			}
			catch ( PDOException $failure ) { 
                $results["meta"]["ok"] = false;
                $results["meta"]["exception"] = $failure;               
            }

		    header("Content-Type: application/json");
		    echo json_encode($results);

		    break;
	}
?>