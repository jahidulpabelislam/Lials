<?php
	include '../../inc/all.php';
	
	//Get the method verb
	$verb = $_SERVER['REQUEST_METHOD'];

	switch ($verb) {
        case "POST":
       		$results = [];
        	try {
        		$me = $_REQUEST['me'];
				$user = $_REQUEST['user'];

				$query = "INSERT INTO Following (Username1, Username2) VALUES ('${me}', '${user}');";
				$row = $db->query($query);

				if (count($row) > 0) {
			        $results['meta']["ok"] = true;
			        $results['meta']["status"] = 201;
			        $results['meta']["msg"] = "Created";
			    }

				$query = "SELECT * FROM Following WHERE Username1 = '${me}' AND Username2 = '${user}';";
			    $following = $db->query($query);
			    $row = $following->fetchAll(PDO::FETCH_ASSOC);
			    
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
				$me = $_REQUEST['me'];
				$user = $_REQUEST['user'];

				$results['meta']["action"] = "delete";

				$query = "DELETE FROM Following WHERE Username1 = '${me}' AND Username2 = '${user}';";
			    $row = $db->query($query);

				$results["meta"]["ok"] = (count($row->rowCount()) > 0);

			    $results["rows"]["user"] = $user;
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