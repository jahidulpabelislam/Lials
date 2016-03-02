<?php
	include '../../inc/all.php';

	//get the method verb
	$verb = $_SERVER['REQUEST_METHOD'];

	//get the path to decide what happens
    $path = explode('/', ltrim($_SERVER['PATH_INFO'], "/"));

	switch ($verb) {
        case "GET":
        	$results = [];
        	try {
				$following = "";
				$me = $_REQUEST['me'];
				if (isset($_REQUEST['user'])) {
					$user = $_REQUEST['user'];
					$where = "Goal.Username = '${user}'";
				}
				else if (isset($_REQUEST['search'])) {
					$search = $_REQUEST['search'];
					$where = "Goal LIKE '%${search}%'";
				}
				else {
					$following = "LEFT JOIN (SELECT * FROM Following WHERE Username1 = '${me}') AS gf ON Goal.Username = Username2";
					$where = "Username1 is not null OR Goal.Username = '${me}'";
				}
				
				$query = "SELECT * FROM Goal ${following} LEFT JOIN (SELECT GoalID FROM Liked WHERE Username = '${me}') AS gl ON ID = GoalID WHERE ${where} ORDER BY Upload, ID;";
				
				$goals = $db->query($query);
				$rows = $goals->fetchAll(PDO::FETCH_ASSOC);

				$results["rows"] = $rows;
				$results["meta"]["ok"] = true;
				$results["meta"]["query"] = $query;
				$results["meta"]["count"] = count($rows);
			}
			catch ( PDOException $failure ) { 
				$results["meta"]["ok"] = false;
        		$results["meta"]["exception"] = $failure;		       	
			}

			header("Content-Type: application/json");
		    echo json_encode($results);

		    break;
		case "POST":
			$results = [];
			try {
				$goal = $_REQUEST['goal'];
				$due = $_REQUEST['due'];
				$upload = date("Y/m/d");
				$me = $_REQUEST['me'];

				$results['meta']["action"] = "insert";

				$query = "INSERT INTO Goal (Goal, Due, Upload, Username) VALUES ('${goal}','${due}','${upload}','${me}');";
			    $row = $db->query($query);

			    if (count($row) > 0) {
			        $results['meta']["ok"] = true;
			        $results['meta']["status"] = 201;
			        $results['meta']["msg"] = "Created";
			    }

			    $id = $db->lastInsertId();

			    $query = "SELECT * FROM Goal LEFT JOIN (SELECT GoalID FROM Liked WHERE Username = '${me}') AS gl ON ID = GoalID WHERE ID = ${id};";
			    $goal = $db->query($query);
			    $rows = $goal->fetchAll(PDO::FETCH_ASSOC);
			    
			    $results["rows"] = $rows;
			}
			catch ( PDOException $failure ) { 
				$results["meta"]["ok"] = false;
        		$results["meta"]["exception"] = $failure;		       	
			}

			header("Content-Type: application/json");
		    echo json_encode($results);

		    break;
		case "PATCH":
			$results = [];
			try {
				$goalID = $path[0];

				if (isset($_REQUEST['completion'])) {
					$results["rows"]["completion"] = "sgsdg";
					$query = "UPDATE Goal SET Complete = NOT Complete WHERE ID = ${goalID};";
				}
				else if (isset($_REQUEST['goal'])) {
					$goal = $_REQUEST['goal'];

				    $query = "UPDATE Goal SET Goal = '${goal}' WHERE ID = ${goalID};";
				}
				$result = $db->query($query);

				$results['meta']["action"] = "update";
				$results['meta']["ok"] = (count($result->rowCount()) > 0);

				$query = "SELECT * FROM Goal WHERE ID=${goalID};";
				$goal = $db->query($query);
				$row = $goal->fetchAll(PDO::FETCH_ASSOC);
				    
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
				$goalID = $path[0];
				
				$results['meta']["action"] = "delete";

				$query = "DELETE FROM Comment WHERE GoalID = ${goalID}; DELETE FROM Liked WHERE GoalID = ${goalID}; DELETE FROM Goal WHERE ID = ${goalID};";
			    $row = $db->query($query);

			    $results["meta"]["ok"] = (count($row->rowCount()) > 0);
			    $results["rows"]["GoalID"] = $goalID;

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