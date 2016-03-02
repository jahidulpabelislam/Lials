<?php
	include '../inc/all.php';

    //get the method verb
	$verb = $_SERVER['REQUEST_METHOD'];
    
    //get the path to decide what happens
    $path = explode('/', ltrim($_SERVER['PATH_INFO'], "/"));

    switch ($path[0]) {
        case "users":
            
            switch ($verb) {
                case "GET":
                    $results = [];
                    try {
                        $username = $path[1];
                        if (isset($_REQUEST['password'])) {
                            $password = $_REQUEST['password'];
                            $query = "SELECT Username FROM User WHERE Username = '${username}' AND Password = '${password}';";
                            $user = $db->query($query);
                                
                            if ($user->rowCount() == 0) {   
                                $query = "SELECT * FROM User WHERE Username = '${username}';";
                                $row = $db->query($query);
                                if ($row->rowCount() == 0) {
                                    $results['meta']['feedback'] = "No user found with that username.";
                                }
                                else {
                                    $results['meta']['feedback'] = "Password is wrong please try again.";
                                }
                            }
                        }
                        else if (isset($_REQUEST['me'])) {
                            $me = $_REQUEST['me'];
                            $query = "SELECT * FROM User LEFT JOIN (SELECT * FROM Following WHERE Username1 = '${me}') AS gf ON Username = Username2 WHERE Username = '${username}';";
                            
                            $user = $db->query($query);
                        }

                        $user = $user->fetchAll(PDO::FETCH_ASSOC);
                        $results["rows"] = $user;
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
                        $username = $_REQUEST['username'];
                        $password = $_REQUEST['password'];
                            
                        $query = "INSERT INTO User (Username, Password) VALUES ('${username}','${password}');";
                        $db->query($query);

                        $query = "SELECT Username FROM User WHERE Username = '${username}';";
                        $user = $db->query($query);

                        $user = $user->fetchAll(PDO::FETCH_ASSOC);
                        $results["rows2"] = $username;
                        $results["rows"] = $user;
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
                        $username = $path[1];
                        
                        $query = "UPDATE User SET Private = NOT Private WHERE Username = '${username}';";
                        $db->query($query);

                        $query = "SELECT * FROM User WHERE Username = '${username}';";
                        $user = $db->query($query);

                        $user = $user->fetchAll(PDO::FETCH_ASSOC);
                        $results["rows"] = $user;
                    }
                    catch ( PDOException $failure ) { 
                        $results["meta"]["ok"] = false;
                        $results["meta"]["exception"] = $failure;               
                    }

                    header("Content-Type: application/json");
                    echo json_encode($results);

                    break;
            }

        break;
    }
?>