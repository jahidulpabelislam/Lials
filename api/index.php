<?php
	include '../inc/all.php';

    //get the method verb
	$verb = $_SERVER['REQUEST_METHOD'];
    //get the path to decide what happens
    $path = explode('/', ltrim($_SERVER['PATH_INFO'], "/"));

    switch ($path[0]) {
        case "users":
            $results = [];
            $password = $_REQUEST['password'];
            switch ($verb) {
                case "GET":
                    $username = $path[1];
        
                    $query = "SELECT username FROM user WHERE username = '${username}' and password = '${password}';";
                    $user = $db->query($query);
                        
                    if ($user->rowCount() == 0) {   
                        $query = "SELECT * FROM user WHERE username = '${username}';";
                        $row = $db->query($query);
                        if ($row->rowCount() == 0) {
                            $results['meta']['feedback'] = "No user found with that username.";
                        }
                        else {
                            $results['meta']['feedback'] = "Password is wrong please try again.";
                        }
                    }

                    break;

                case "POST":
                    $username = $_POST['username'];
                        
                    $query = "INSERT INTO user (username, password) VALUES ('${username}','${password}')";
                    $db->query($query);

                    $username = $db->lastInsertId();

                    $query = "SELECT username FROM user WHERE username = '${username}';";
                    $user = $db->query($query);

                    break;
            }

        $user = $user->fetchAll(PDO::FETCH_ASSOC);
        $results["rows"] = $user;
        header("Content-Type: application/json");
        echo json_encode($results);
        break;
    }
?>