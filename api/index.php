<?php
include '../inc/all.php';

//get the method verb
$verb = $_SERVER['REQUEST_METHOD'];

//get the path to decide what happens
$path = explode('/', ltrim($_SERVER['PATH_INFO'], "/"));

//gets the data into array
$requests = $_REQUEST;

switch ($path[0]) {
    case "users":
        switch ($verb) {
            case "GET":
                $requests["username"] = $path[1];
                $results = getUser($requests);
                break;
            case "POST":
                $results = addUser($requests);
                break;
            case "PATCH":
                $requests["username"] = $path[1];
                $results = editUser($requests);
                break;
        }
        break;
    case "goals":
        switch ($verb) {
            case "GET":
                $results = getGoals($requests);
                break;
            case "POST":
                $results = addGoal($requests);
                break;
            case "PATCH":
                $requests["goalID"] = $path[1];
                $results = editGoal($requests);
                break;
            case "DELETE":
                $requests["goalID"] = $path[1];
                $results = deleteGoal($requests);
                break;
        }
        break;
    case "comments":
        switch ($verb) {
            case "GET":
                $results = getComments($requests);
                break;
            case "POST":
                $results = addComment($requests);
                break;
            case "PATCH":
                $requests["commentID"] = $path[1];
                $results = editComment($requests);
                break;
            case "DELETE":
                $requests["commentID"] = $path[1];
                $results = deleteComment($requests);
                break;
        }
        break;
    case "follows":
        switch ($verb) {
            case "POST":
                $results = addFriend($requests);
                break;
            case "DELETE":
                $results = deleteFriend($requests);
                break;
        }
        break;
    case "likes":
        switch ($verb) {
            case "POST":
                $results = addLike($requests);
                break;
            case "DELETE":
                $results = deleteLike($requests);
                break;
        }
        break;
    case "pictures":
        switch ($verb) {
            case "POST":
                $results = addPicture($requests);
                break;
            case "DELETE";
                $results = deletePicture($requests);
                break;
        }
        break;
}
$status = $results["status"];
$message = $results["message"];
header("HTTP/1.1 $status $message");

header("Content-Type: application/json");
echo json_encode($results);

function getUser($requests)
{
    $db = new pdodb;
    $results = [];
    if (isset($requests['password'])) {
        $query = "SELECT Username FROM User WHERE Username = ? AND Password = ?;";
        $bindings = array($requests["username"], $requests['password']);
        $user = $db->query($query, $bindings);
        if ($user == 0) {
            $query = "SELECT * FROM User WHERE Username = ?;";
            $bindings = array($requests["username"]);
            $row = $db->query($query, $bindings);
            if ($row == 0) {
                $results['meta']['feedback'] = "No user found with that username.";
            } else {
                $results['meta']['feedback'] = "Password is wrong please try again.";
            }
        }
    } else if (isset($requests['me'])) {
        $query = "SELECT * FROM User LEFT JOIN (SELECT * FROM Following WHERE Username1 = ?) AS gf ON Username = Username2 WHERE Username = ?;";
        $bindings = array($requests["me"], $requests['me']);
        $user = $db->query($query, $bindings);
    }

    $results["rows"] = $user;

    return $results;
}

function addUser($requests)
{
    $db = new pdodb;
    $results = [];

    $query = "INSERT INTO User (Username, Password) VALUES (?,?);";
    $bindings = array($requests["username"], $requests["password"]);
    $user = $db->query($query, $bindings);

    $query = "SELECT Username FROM User WHERE Username = ?;";
    $bindings = array($requests["username"]);
    $user = $db->query($query, $bindings);

    $results["rows"] = $user;

    return $results;
}

function editUser($requests)
{
    $db = new pdodb;
    $results = [];

    $query = "UPDATE User SET Private = NOT Private WHERE Username = ?;";
    $bindings = array($requests["username"]);
    $db->query($query, $bindings);

    $query = "SELECT Private FROM User WHERE Username = ?;";
    $bindings = array($requests["username"]);
    $user = $db->query($query, $bindings);

    $results["rows"] = $user;

    return $results;
}

function getGoals($requests)
{
    $db = new pdodb;
    $results = [];
    $following = "";
    $me = $requests['me'];
    if (isset($requests['user'])) {
        $user = $requests['user'];
        $where = "Goal.Username = '${user}'";
    } else if (isset($requests['search'])) {
        $searches = $requests['search'];
        $search2 = "";
        $searches = explode(" ", $searches);
        foreach ($searches as $search) {
            $search2 .= "${search}%";
        }
        $where = "Goal LIKE '%${search2}'";
    } else {
        $following = "LEFT JOIN (SELECT * FROM Following WHERE Username1 = '${me}') AS gf ON Goal.Username = Username2";
        $where = "Username1 is not null OR Goal.Username = '${me}'";
    }

    $query = "SELECT * FROM Goal ${following} LEFT JOIN (SELECT GoalID FROM Liked WHERE Username = '${me}') AS gl ON ID = GoalID WHERE ${where} ORDER BY Upload, ID;";

    $goals = $db->query($query);

    $results["rows"] = $goals;
    $results["meta"]["ok"] = true;
    $results["meta"]["query"] = $query;
    $results["meta"]["count"] = count($goals);

    return $results;
}

function addGoal($requests)
{
    $db = new pdodb;
    $results = [];

    $results['meta']["request"] = $requests;
    $results['meta']["action"] = "insert";

    $query = "INSERT INTO Goal (Goal, Due, Upload, Username) VALUES (?,?,?,?);";
    $bindings = array($requests["goal"], $requests["due"], date("y/m/d"), $requests["me"]);
    $goal = $db->query($query, $bindings);

    if ($goal > 0) {
        $results['meta']["ok"] = true;
        $results['meta']["status"] = 201;
        $results['meta']["msg"] = "Created";
    }

    $id = $db->lastInsertId();

    $query = "SELECT * FROM Goal LEFT JOIN (SELECT GoalID FROM Liked WHERE Username = ?) AS gl ON ID = GoalID WHERE ID = ?;";
    $bindings = array($requests["me"], $id);
    $goal = $db->query($query, $bindings);

    $results["rows"] = $goal;

    return $results;
}

function editGoal($requests)
{
    $db = new pdodb;
    $results = [];

    if (isset($requests['completion'])) {
        $query = "UPDATE Goal SET Complete = NOT Complete WHERE ID = ?;";
        $bindings = array($requests["goalID"]);
    } else if (isset($requests['goal'])) {
        $query = "UPDATE Goal SET Goal = ? WHERE ID = ?;";
        $bindings = array($requests["goal"], $requests["goalID"]);
    }
    $result = $db->query($query, $bindings);

    $results['meta']["action"] = "update";
    $results['meta']["ok"] = ($result > 0);

    $query = "SELECT * FROM Goal WHERE ID = ?;";
    $bindings = array($requests["goalID"]);
    $goal = $db->query($query, $bindings);

    $results["rows"] = $goal;

    return $results;
}

function deleteGoal($requests)
{
    $db = new pdodb;
    $results = [];
    $results['meta']["action"] = "delete";

    $query = "DELETE FROM Comment WHERE GoalID = ?; DELETE FROM Liked WHERE GoalID = ?; DELETE FROM Goal WHERE ID = ?;";
    $bindings = array($requests["goalID"], $requests["goalID"], $requests["goalID"]);
    $row = $db->query($query, $bindings);
     if ($row > 0) {
         $results["meta"]["ok"] = true;
         $results["rows"]["GoalID"] = $requests["goalID"];
     }

    return $results;
}

function getComments($requests)
{
    $db = new pdodb;
    $results = [];

    $query = "SELECT * FROM Comment WHERE GoalID = ? ORDER BY Upload, ID;";
    $bindings = array($requests["goalID"]);
    $comments = $db->query($query, $bindings);

    $results["meta"]["ok"] = true;
    $results["meta"]["query"] = $query;
    $results["meta"]["count"] = count($comments);

    $results["rows"] = $comments;

    return $results;
}

function addComment($requests)
{
    $db = new pdodb;
    $results = [];

    $results['meta']["requests"] = $requests;
    $results['meta']["action"] = "insert";

    $query = "INSERT INTO Comment (Comment, Goalid, Username, Upload) VALUES (?,?,?,?);";
    $bindings = array($requests["comment"], $requests["goalID"], $requests["username"], date("y/d/m"));
    $comment = $db->query($query, $bindings);

    if ($comment > 0) {
        $results['meta']["ok"] = true;
        $results['meta']["status"] = 201;
        $results['meta']["msg"] = "Created";

        $query = "SELECT * FROM Comment LEFT JOIN (SELECT * FROM Following WHERE Username1 = ?) AS gf ON Comment.Username = username2 WHERE ID = ?;";
        $bindings = array($requests["username"], $db->lastInsertId());
        $comment = $db->query($query, $bindings);

        $results["rows"] = $comment;
    }

    return $results;
}

function editComment($requests)
{
    $db = new pdodb;
    $results = [];

    $results['meta']["action"] = "update";

    $query = "UPDATE Comment SET Comment = ? WHERE ID = ?;";
    $bindings = array($requests["comment"], $requests["commentID"]);
    $row = $db->query($query, $bindings);

    if ($row > 0) {
        $results['meta']["ok"] = true;

        $query = "SELECT * FROM Comment WHERE ID = ?;";
        $bindings = array($requests["commentID"]);
        $comment = $db->query($query, $bindings);

        $results["rows"] = $comment;
    }

    return $results;
}

function deleteComment($requests)
{
    $db = new pdodb;
    $results = [];

    $results['meta']["action"] = "delete";

    $query = "DELETE FROM Comment WHERE ID = ?;";
    $bindings = array($requests["commentID"]);
    $row = $db->query($query, $bindings);
    if ($row > 0) {
        $results["meta"]["ok"] = true;
        $results["rows"]["commentID"] = $requests["commentID"];
    }


    return $results;
}

function addFriend($requests)
{
    $db = new pdodb;
    $results = [];

    $results['meta']["request"] = $requests;
    $results['meta']["action"] = "insert";

    $query = "INSERT INTO Following (Username1, Username2) VALUES (? ?);";
    $bindings = array($requests["me"], $requests["user"]);
    $follow = $db->query($query, $bindings);

    if ($follow > 0) {
        $results['meta']["ok"] = true;
        $results['meta']["status"] = 201;
        $results['meta']["msg"] = "Created";

        $query = "SELECT * FROM Following WHERE Username1 = ? AND Username2 = ?;";
        $bindings = array($requests["me"], $requests["user"]);
        $follow = $db->query($query, $bindings);

        $results["rows"] = $follow;
    }

    return $results;
}

function deleteFriend($requests)
{
    $db = new pdodb;
    $results = [];

    $results['meta']["action"] = "delete";

    $query = "DELETE FROM Following WHERE Username1 = ? AND Username2 = ?;";
    $bindings = array($requests["me"], $requests["user"]);
    $row = $db->query($query, $bindings);

    if ($row > 0) {
        $results["meta"]["ok"] = true;

        $results["rows"]["user"] = $requests["user"];
    }

    return $results;
}

function addLike($requests)
{
    $db = new pdodb;
    $results = [];

    $results['meta']["request"] = $_REQUEST;
    $results['meta']["action"] = "insert";

    $query = "INSERT INTO Liked (Username, goalID) VALUES (?, ?);";
    $bindings = array($requests["me"], $requests["goalID"]);
    $like = $db->query($query, $bindings);

    if ($like > 0) {
        $results['meta']["ok"] = true;
        $results['meta']["status"] = 201;
        $results['meta']["msg"] = "Created";

        $query = "SELECT * FROM Liked WHERE Username = ? AND goalID = ?;";
        $bindings = array($requests["me"], $requests["goalID"]);
        $like = $db->query($query, $bindings);

        $results["rows"] = $like;
    }

    return $results;
}

function deleteLike($requests)
{
    $db = new pdodb;
    $results = [];

    $results['meta']["action"] = "delete";

    $query = "DELETE FROM Liked WHERE Username = ? AND GoalID = ?;";
    $bindings = array($requests["me"], $requests["goalID"]);
    $row = $db->query($query, $bindings);
    if ($row > 0) {
        $results["meta"]["ok"] = true;

        $results["rows"]["goalID"] = $requests["goalID"];
    }

    return $results;
}

function addPicture($requests)
{
    $db = new pdodb;
    $results = [];
    $results["meta"]["request"] = $requests;
    $results["meta"]["files"] = $_FILES;
    $results['meta']["action"] = "insert";

    $username = $requests['username'];

    $directory = "uploads/";

    $imageFileType = pathinfo(basename($_FILES["picture"]["name"]), PATHINFO_EXTENSION);
    $file = $directory . $username . "." . $imageFileType;

    //check if image file is a actual image or fake image
    $imagetype = getimagesize($_FILES["picture"]["tmp_name"]);
    if ($imagetype !== false) {
        // if everything is ok, try to upload file
        if (move_uploaded_file($_FILES["picture"]["tmp_name"], $file)) {
            $query = "UPDATE User SET Picture = ? WHERE Username = ?;";
            $bindings = array($file, $username);
            $picture = $db->query($query, $bindings);

            if ($picture > 0) {
                $results['meta']["ok"] = true;
                $results['meta']["status"] = 201;
                $results['meta']["msg"] = "Created";

                $query = "SELECT Username, Picture FROM User WHERE Username = ?;";
                $bindings = array($username);
                $picture = $db->query($query, $bindings);

                $results["rows"] = $picture;
            }

        } else {
            $results["meta"]["feedback"] = "Sorry, there was an error uploading your file.";
        }
    } else {
        $results["meta"]['feedback'] = "File is not an image.";
    }

    return $results;
}

function deletePicture($requests)
{
    $db = new pdodb;
    $results = [];
    $results["meta"]["request"] = $requests;
    $results['meta']["action"] = "delete";

    $query = "UPDATE User SET Picture = null WHERE Username = ?;";
    $bindings = array($requests["username"]);
    $picture = $db->query($query, $bindings);

    if ($picture > 0) {
        $results['meta']["ok"] = true;
        $results['meta']["status"] = 200;
        $results['meta']["msg"] = "deleted";

        $query = "SELECT * FROM User WHERE Username = ?;";
        $bindings = array($requests["username"]);
        $picture = $db->query($query, $bindings);

        $results["rows"] = $picture;
    }

    return $results;
}