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
                $results["afdaf"]= $requests;
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
    $username = $requests["username"];
    $password = $requests["password"];

    $query = "INSERT INTO User (Username, Password) VALUES ('${username}','${password}');";
    $user = $db->query($query);

    $query = "SELECT Username FROM User WHERE Username = '${username}';";
    $user = $db->query($query);

    $results["rows"] = $user;

    return $results;
}

function editUser($requests)
{
    $db = new pdodb;
    $results = [];
    $username = $requests["username"];

    $query = "UPDATE User SET Private = NOT Private WHERE Username = '${username}';";
    $db->query($query);

    $query = "SELECT Private FROM User WHERE Username = '${username}';";
    $user = $db->query($query);

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
    $goal = $requests['goal'];
    $due = $requests['due'];
    $upload = date("Y/m/d");
    $me = $requests['me'];

    $results['meta']["request"] = $requests;
    $results['meta']["action"] = "insert";

    $query = "INSERT INTO Goal (Goal, Due, Upload, Username) VALUES ('${goal}','${due}','${upload}','${me}');";
    $goal = $db->query($query);

    if ($goal > 0) {
        $results['meta']["ok"] = true;
        $results['meta']["status"] = 201;
        $results['meta']["msg"] = "Created";
    }

    $id = $db->lastInsertId();

    $query = "SELECT * FROM Goal LEFT JOIN (SELECT GoalID FROM Liked WHERE Username = '${me}') AS gl ON ID = GoalID WHERE ID = ${id};";
    $goal = $db->query($query);

    $results["rows"] = $goal;

    return $results;
}

function editGoal($requests)
{
    $db = new pdodb;
    $results = [];
    $goalID = $requests["goalID"];

    if (isset($requests['completion'])) {
        $results["rows"]["completion"] = "sgsdg";
        $query = "UPDATE Goal SET Complete = NOT Complete WHERE ID = ${goalID};";
    } else if (isset($requests['goal'])) {
        $goal = $_REQUEST['goal'];

        $query = "UPDATE Goal SET Goal = '${goal}' WHERE ID = ${goalID};";
    }
    $result = $db->query($query);

    $results['meta']["action"] = "update";
    $results['meta']["ok"] = ($result > 0);

    $query = "SELECT * FROM Goal WHERE ID=${goalID};";
    $goal = $db->query($query);

    $results["rows"] = $goal;

    return $results;
}

function deleteGoal($requests)
{
    $db = new pdodb;
    $results = [];
    $goalID = $requests["goalID"];

    $results['meta']["action"] = "delete";

    $query = "DELETE FROM Comment WHERE GoalID = ${goalID}; DELETE FROM Liked WHERE GoalID = ${goalID}; DELETE FROM Goal WHERE ID = ${goalID};";
    $row = $db->query($query);

    $results["meta"]["ok"] = (count($row) > 0);
    $results["rows"]["GoalID"] = $goalID;

    return $results;
}

function getComments($requests)
{
    $db = new pdodb;
    $results = [];
    $goalID = $requests['goalID'];

    $query = "SELECT * FROM Comment WHERE GoalID = '${goalID}' ORDER BY Upload, ID;";
    $comments = $db->query($query);

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
    $goalid = $requests['goalID'];
    $comment = $requests['comment'];
    $username = $requests['me'];
    $upload = date("Y/m/d");

    $results['meta']["requests"] = $requests;
    $results['meta']["action"] = "insert";

    $query = "INSERT INTO Comment (Comment, Goalid, Username, Upload) VALUES ('${comment}',${goalid},'${username}','${upload}');";
    $comment = $db->query($query);

    if ($comment > 0) {
        $results['meta']["ok"] = true;
        $results['meta']["status"] = 201;
        $results['meta']["msg"] = "Created";
    }

    $id = $db->lastInsertId();

    $query = "SELECT * FROM Comment LEFT JOIN (SELECT * FROM Following WHERE Username1 = '${username}') AS gf ON Comment.Username = username2 WHERE ID=${id};";
    $comment = $db->query($query);

    $results["rows"] = $comment;

    return $results;
}

function editComment($requests)
{
    $db = new pdodb;
    $results = [];
    $commentID = $requests["commentID"];
    $comment = $requests['comment'];

    $query = "UPDATE Comment SET Comment = '${comment}' WHERE ID = ${commentID};";
    $row = $db->query($query);

    $results['meta']["action"] = "update";
    $results['meta']["ok"] = ($row > 0);

    $query = "SELECT * FROM Comment WHERE ID = ${commentID};";
    $comment = $db->query($query);

    $results["rows"] = $comment;

    return $results;
}

function deleteComment($requests)
{
    $db = new pdodb;
    $results = [];
    $commentID = $requests["commentID"];

    $results['meta']["action"] = "delete";

    $query = "DELETE FROM Comment WHERE ID = ${commentID};";
    $row = $db->query($query);

    $results["meta"]["ok"] = ($row > 0);
    $results["rows"]["commentID"] = $commentID;

    return $results;
}

function addFriend($requests)
{
    $db = new pdodb;
    $results = [];
    $me = $requests['me'];
    $user = $requests['user'];

    $results['meta']["request"] = $requests;
    $results['meta']["action"] = "insert";

    $query = "INSERT INTO Following (Username1, Username2) VALUES ('${me}', '${user}');";
    $follow = $db->query($query);

    if ($follow > 0) {
        $results['meta']["ok"] = true;
        $results['meta']["status"] = 201;
        $results['meta']["msg"] = "Created";
    }

    $query = "SELECT * FROM Following WHERE Username1 = '${me}' AND Username2 = '${user}';";
    $follow = $db->query($query);

    $results["rows"] = $follow;

    return $results;
}

function deleteFriend($requests)
{
    $db = new pdodb;
    $results = [];
    $me = $requests['me'];
    $user = $requests['user'];

    $results['meta']["action"] = "delete";

    $query = "DELETE FROM Following WHERE Username1 = '${me}' AND Username2 = '${user}';";
    $row = $db->query($query);

    $results["meta"]["ok"] = ($row > 0);

    $results["rows"]["user"] = $user;

    return $results;
}

function addLike($requests)
{
    $db = new pdodb;
    $results = [];

    $goalID = $requests['goalID'];
    $me = $requests['me'];

    $results['meta']["request"] = $_REQUEST;
    $results['meta']["action"] = "insert";

    $query = "INSERT INTO Liked (Username, goalID) VALUES ('${me}', ${goalID});";
    $like = $db->query($query);

    if ($like > 0) {
        $results['meta']["ok"] = true;
        $results['meta']["status"] = 201;
        $results['meta']["msg"] = "Created";
    }

    $query = "SELECT * FROM Liked WHERE Username = '${me}' AND goalID = ${goalID};";
    $like = $db->query($query);

    $results["rows"] = $like;

    return $results;
}

function deleteLike($requests)
{
    $db = new pdodb;
    $results = [];
    $goalID = $requests['goalID'];
    $me = $requests['me'];

    $results['meta']["action"] = "delete";

    $query = "DELETE FROM Liked WHERE Username = '${me}' AND GoalID = ${goalID};";
    $row = $db->query($query);

    $results["meta"]["ok"] = ($row > 0);

    $results["rows"]["goalID"] = $goalID;

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
            $query = "UPDATE User SET Picture = '${file}' WHERE Username = '${username}';";
            $picture = $db->query($query);

            if ($picture > 0) {
                $results['meta']["ok"] = true;
                $results['meta']["status"] = 201;
                $results['meta']["msg"] = "Created";
            }

            $query = "SELECT Username, Picture FROM User WHERE Username = '${username}';";
            $picture = $db->query($query);


            $results["rows"] = $picture;
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

    $username = $requests['username'];

    $query = "UPDATE User SET Picture = null WHERE Username = '${username}';";
    $picture = $db->query($query);

    if ($picture > 0) {
        $results['meta']["ok"] = true;
        $results['meta']["status"] = 200;
        $results['meta']["msg"] = "deleted";
    }

    $query = "SELECT * FROM User WHERE Username = '${username}';";
    $picture = $db->query($query);

    $results["rows"] = $picture;

    return $results;
}