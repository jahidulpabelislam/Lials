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
				$goalID = $_REQUEST['goalID'];
						
				$query = "SELECT * FROM Comment WHERE GoalID = '${goalID}' ORDER BY Upload, ID;";
				$comments = $db->query($query);
				$rows = $comments->fetchAll(PDO::FETCH_ASSOC);
				
				$results["meta"]["ok"] = true;
				$results["meta"]["query"] = $query;
				$results["meta"]["count"] = count($rows);

				$results["rows"] = $rows;
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
				$goalid = $_REQUEST['goalID'];
				$comment = $_REQUEST['comment'];
				$username = $_REQUEST['me'];
				$upload = date("Y/m/d");

				$query = "INSERT INTO Comment (Comment, Goalid, Username, Upload) VALUES ('${comment}',${goalid},'${username}','${upload}');";
			    $db->query($query);

			    if (count($row) > 0) {
			        $results['meta']["ok"] = true;
			        $results['meta']["status"] = 201;
			        $results['meta']["msg"] = "Created";
			    }

			    $id = $db->lastInsertId();

			    $query = "SELECT * FROM Comment LEFT JOIN (SELECT * FROM Following WHERE Username1 = '${username}') AS gf ON Comment.Username = username2 WHERE ID=${id};";
			    $comment = $db->query($query);
			    $row = $comment->fetchAll(PDO::FETCH_ASSOC);

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
				$commentID = $path[0];
				
				$results['meta']["action"] = "delete";

				$query = "DELETE FROM Comment WHERE ID = ${commentID};";
    			$row = $db->query($query);

    			$results["meta"]["ok"] = (count($row->rowCount()) > 0);
    			$results["rows"]["commentID"] = $commentID;
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
	    		$commentID = $path[0];
				$comment = $_REQUEST['comment'];
						
				$query = "UPDATE Comment SET Comment = '${comment}' WHERE ID = ${commentID};";
			    $row = $db->query($query);

			    $results['meta']["action"] = "update";
				$results['meta']["ok"] = (count($row->rowCount()) > 0);

			    $query = "SELECT * FROM Comment WHERE ID = ${commentID};";
				$comment = $db->query($query);
				$rows = $comment->fetchAll(PDO::FETCH_ASSOC);

				$results["rows"] = $rows;
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