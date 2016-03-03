<?php
    include '../../inc/all.php';

    //get the method verb
    $verb = $_SERVER['REQUEST_METHOD'];

    switch ($verb) {
        case "POST":
            $results = [];
            $results["meta"]["request"] = $_REQUEST;
            $results["meta"]["files"] = $_FILES;
            
            $username = $_REQUEST['username'];

            $directory = "uploads/";
            
            $imageFileType = pathinfo(basename($_FILES["picture"]["name"]),PATHINFO_EXTENSION);
            $file = $directory . $username . "." . $imageFileType;

            //check if image file is a actual image or fake image
            $imagetype = getimagesize($_FILES["picture"]["tmp_name"]);
            if($imagetype !== false) {         
                // if everything is ok, try to upload file
                if (move_uploaded_file($_FILES["picture"]["tmp_name"], $file)) {
                    $query = "UPDATE User SET Picture = '${file}' WHERE Username = '${username}';";
                    $db->query($query);

                    $query = "SELECT * FROM User WHERE Username = '${username}';";
                    $row = $db->query($query);

                    $row = $row->fetchAll(PDO::FETCH_ASSOC);

                    $results["rows"] = $row;
                    $results["meta"]["feedback"] = "The file ". basename( $_FILES["picture"]["name"]). " has been uploaded.";
                } 
                else {
                    $results["meta"]["feedback"] = "Sorry, there was an error uploading your file.";
                }
            }
            else {
                $results["meta"]['feedback'] = "File is not an image.";
            }

            header("Content-Type: application/json");
            echo json_encode($results);

            break;

        case "DELETE":
			$results = [];
            try {
                $username = $_REQUEST['username'];

                $query = "UPDATE User SET Picture = null WHERE Username = '${username}';";
                $user = $db->query($query); 

                $query = "SELECT * FROM User WHERE Username = '${username}';";
                $row = $db->query($query);

                $results["rows"] = $row;
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