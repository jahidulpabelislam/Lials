<?php
/*
 * All the functions for API
 * @author 733474
*/

//contains the different functions for the API

/**
 * Check if all data needed is provided
 * And data provided is not empty
 * @param $data array array of data provided
 * @param $dataNeeded array array of data needed
 * @return bool whether data provided is valid and data needed is provided
 */
function checkData($data, $dataNeeded)
{

    //loops through each request needed
    foreach ($dataNeeded as $aData) {

        //checks if data needed is provided and is not empty
        if (!isset($data[$aData]) || trim($data[$aData]) == "") {
            //return false as data needed is not provided or empty
            return false;
        }

    }

    //otherwise data provided are ok and data needed are provided
    return true;
}

/**
 * When the method provided is not allowed
 * @param $method string the method tried
 * @param $path string the path tried
 * @return array array of meta data
 */
function methodNotAllowed($method, $path)
{
    $meta["ok"] = false;
    $meta["status"] = 405;
    $meta["message"] = "Method not allowed.";
    $meta["feedback"] = "${method} Method Not Allowed on ${path}.";
    return $meta;
}

/**
 * Send necessary meta data back when needed data is not provided
 * @param $dataNeeded array array of data needed
 * @return array array of meta data
 */
function dataNotProvided($dataNeeded)
{
    $meta["ok"] = false;
    $meta["status"] = 400;
    $meta["message"] = "Bad Request";
    $meta["requestsNeeded"] = $dataNeeded;
    $meta["feedback"] = "The necessary data was not provided.";
    return $meta;
}

//get a particular user defined by $username
function getAUser($username)
{
    $db = new pdodb;
    $query = "SELECT * FROM User WHERE Username = :username;";
    $bindings = array(':username' => $username);
    return $db->query($query, $bindings);
}

//send necessary meta data back when username provided is not correct
function noUserFound($username)
{
    $meta["ok"] = false;
    $meta["status"] = 404;
    $meta["feedback"] = "No user found with ${username} as username.";
    $meta["message"] = "Not Found";
    return $meta;
}

//get a particular goal defined by $goalID
function getGoal($goalID)
{
    $db = new pdodb;
    $query = "SELECT * FROM Goal WHERE ID = :goalID;";
    $bindings = array(':goalID' => $goalID);
    return $db->query($query, $bindings);
}

//send necessary meta data back when ID of goal provided is not correct
function noGoalFound($goalID)
{
    $meta["ok"] = false;
    $meta["status"] = 404;
    $meta["feedback"] = "No goal found with ${goalID} as ID.";
    $meta["message"] = "Not Found";
    return $meta;
}

//get a particular comment defined by $commentID
function getComment($commentID)
{
    $db = new pdodb;
    $query = "SELECT * FROM Comment WHERE ID = :commentID;";
    $bindings = array('commentID' => $commentID);
    return $db->query($query, $bindings);
}

//send necessary meta data back when ID of comment provided is not correct
function noCommentFound($commentID)
{
    $meta["ok"] = false;
    $meta["status"] = 404;
    $meta["feedback"] = "No Comment found with ${commentID} as ID.";
    $meta["message"] = "Not Found";
    return $meta;
}

//gets a user, either trying to log in or trying to get information of a user
function getUser($data)
{
    $results = [];
    
    //checks if data needed are present and not empty
    $dataNeeded = array("username");
    if (checkData($data, $dataNeeded)) {

        //checks if user exists
        $user = getAUser($data["username"]);
        if ($user["count"] > 0) {

            $db = new pdodb;

            //checks if it includes a password which means user is trying to log in
            if (isset($data["password"])) {

                //get the user with username and password provided
                $query = "SELECT Username FROM User WHERE Username = :username AND Password = :password;";
                $bindings = array(":username" => $data["username"], ":password" => $data["password"]);
                $user = $db->query($query, $bindings);

                //checks if user gave correct password
                if ($user["count"] > 0) {
                    $results = $user;
                    $results["meta"]["ok"] = true;
                }
                //else something gone wrong
                else {

                    //check if database provided any meta data if so problem with executing query
                    if (isset($user["meta"])) {
                        $results = $user;
                    }
                    //else its Unauthorized as the user exists but password is wrong
                    else {
                        $results["meta"]["ok"] = false;
                        $results["meta"]["status"] = 401;
                        $results["meta"]["message"] = "Unauthorized";
                        $results["meta"]["feedback"] = "Password is wrong please try again.";
                    }

                }

            }
            //else checks if "me" was provided which means user is trying to get a another users info
            else if (isset($data["me"])) {

                //checks if user exists
                $user = getAUser($data["me"]);
                if ($user["count"] > 0) {

                    //gets the users info
                    $query = "SELECT Username, Picture, Private, Following FROM User LEFT JOIN (SELECT Following FROM Follow WHERE Username1 = :me) AS u ON Username = Following WHERE Username = :username;";
                    $bindings = array(":me" => $data["me"], ":username" => $data["username"]);
                    $results = $db->query($query, $bindings);

                    $results["meta"]["ok"] = true;
                }
                //something gone wrong getting user
                else {

                    //check if database provided any meta data if so problem with executing query
                    if (isset($user["meta"])) {
                        $results = $user;
                    }
                    //else no user found
                    else {
                        $results["meta"] = noUserFound($data["me"]);
                    }

                }

            }

        }
        //something gone wrong getting user
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($data["username"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//add a new user a user has attempted to add
function addUser($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("username", "password");
    if (checkData($data, $dataNeeded)) {

        //checks if user already exists with username
        $user = getAUser($data["username"]);
        if ($user["count"] == 0) {

            $db = new pdodb;
            $query = "INSERT INTO User (Username, Password) VALUES (:username,:password);";
            $bindings = array(":username" => $data["username"], ":password" => $data["password"]);
            $user = $db->query($query, $bindings);

            //if add was ok
            if ($user["count"] > 0) {
                $results = getAUser($data["username"]);
                $results["meta"]["ok"] = true;
                $results["meta"]["status"] = 201;
                $results["meta"]["message"] = "Created";
            }
            //else error adding user
            else {

                //check if database provided any meta data if so problem with executing query
                if (isset($user["meta"])) {
                    $results = $user;
                }
                //else couldn't add user for unknown reason
                else {
                    $results["meta"]["ok"] = false;
                }

            }

        }
        //else user already exists with username
        else {
            $results["meta"]["ok"] = false;
            $results["meta"]["status"] = 409;
            $results["meta"]["feedback"] = "${data["username"]} Already Taken.";
            $results["meta"]["message"] = "Conflict";
        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//edits a user private attribute
function editUser($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("username");
    if (checkData($data, $dataNeeded)) {

        //checks if user provided exists
        $user = getAUser($data["username"]);
        if ($user["count"] > 0) {

            $db = new pdodb;
            $query = "UPDATE User SET Private = NOT Private WHERE Username = :username;";
            $bindings = array(":username" => $data["username"]);
            $user = $db->query($query, $bindings);

            //if update was ok
            if ($user["count"] > 0) {
                $results = getAUser($data["username"]);
                $results["meta"]["ok"] = true;
            }
            //else error updating user
            else {

                //check if database provided any meta data if so problem with executing query
                if (isset($user["meta"])) {
                    $results = $user;
                }
                //else couldn't edit user for unknown reason
                else {
                    $results["meta"]["ok"] = false;
                }

            }

        }
        //else error getting user
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($data["username"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//gets goals , either of a user or filtered by a search or goals of other users user is following plus theirs
function getGoals($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("me");
    if (checkData($data, $dataNeeded)) {

        //checks if user provided exists
        $user = getAUser($data["me"]);
        if ($user["count"] > 0) {

            $bindings = array();
            $following = "";

            //if it includes a username of a user to get goals of
            if (isset($data["user"]) && trim($data["user"]) != "") {

                //checks if user provided exists
                $user = getAUser($data["user"]);
                if ($user["count"] > 0) {
                    $where = "Goal.Username = :user";
                    $bindings[":user"] = $data["user"];
                }
                //else error getting user
                else {

                    //check if database provided any meta data if so problem with executing query
                    if (isset($user["meta"])) {
                        $results = $user;
                    }
                    //else no user found
                    else {
                        $results["meta"] = noUserFound($data["user"]);
                    }

                }

            }
            //if it includes a search
            else if (isset($data["search"]) && trim($data["search"]) != "") {

                //split each word in search
                $searches = explode(" ", $data["search"]);

                //check if there's a 'NOT' in search
                $not = array_search("NOT", $searches, true);

                //check if there's a "OR" in search
                $or = array_search("OR", $searches, true);

                //if using a NOT in query
                if (($not !== false) && ($not > 0) && ($not < count($searches) - 1)) {

                    $not1 = $not2 = "%";
                    //loop through each search word
                    foreach ($searches as $aSearch) {

                        ///loop through each search word
                        $aSearchPosition = array_search($aSearch, $searches, true);

                        //check if search is before NOT or after
                        if ($aSearchPosition < $not) {
                            $not1 .= "${aSearch}%";
                        } else if ($aSearchPosition > $not) {
                            $not2 .= "${aSearch}%";
                        }

                        //remove asearch
                        unset($searches[$aSearchPosition]);

                    }

                    $where = "Goal LIKE :not1 AND Goal NOT LIKE :not2";
                    $bindings[":not1"] = $not1;
                    $bindings[":not2"] = $not2;

                }
                //if using a OR in query
                else if ($or !== false && $or > 0 && $or < count($searches) - 1) {

                    $or1 =  $or2 = "%";
                    //loop through each search word
                    foreach ($searches as $aSearch) {

                        //loop through each search word
                        $aSearchPosition = array_search($aSearch, $searches, true);

                        //check if search is before OR or after
                        if ($aSearchPosition < $or) {
                            $or1 .= "${aSearch}%";
                        } else if ($aSearchPosition > $or) {
                            $or2 .= "${aSearch}%";
                        }

                        //remove asearch
                        unset($searches[$aSearchPosition]);
                    }

                    $where = "Goal LIKE :or1 OR Goal LIKE :or2";
                    $bindings[":or1"] = $or1;
                    $bindings[":or2"] = $or2;

                }
                //else assume search is for a general search
                else {

                    $search = "%";

                    //loop through each search word
                    foreach ($searches as $aSearch) {
                        $search .= "${aSearch}%";
                    }

                    $where = "Goal LIKE :search";
                    $bindings[":search"] = $search;
                }

            }
            //if not search or username provided assumes goals of users they are following
            else {
                $following = "LEFT JOIN (SELECT * FROM Follow WHERE Username1 = :me) AS Follow ON Username = Following";
                $where = "Username1 IS NOT NULL OR Goal.Username = :me";
            }

            if (isset($where)) {

                $db = new pdodb;
                $query = "SELECT * FROM Goal ${following} LEFT JOIN (SELECT GoalID, Liked FROM Likes WHERE Username = :me) AS GoalLike ON ID = GoalID WHERE ${where} ORDER BY Upload, ID;";
                $bindings[":me"] = $data["me"];
                $goals = $db->query($query, $bindings);

                //check if database provided any meta data if so problem with executing query
                if (isset($goals["meta"])) {
                    $results = $goals;
                }
                //else query was alright so return data
                else {
                    $results = $goals;
                    $results["meta"]["ok"] = true;
                }

            }

        }
        //else error getting user
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($data["me"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//add a goal user has attempted to add
function addGoal($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("goal", "due", "me");
    if (checkData($data, $dataNeeded)) {

        //check if user provided exists
        $user = getAUser($data["me"]);
        if (count($user["rows"]) > 0) {

            //checks if email provided is valid using REGEX
            if (preg_match("/\b[\d]{4}-[\d]{2}-[\d]{2}\b/im", $data["due"])) {

                $db = new pdodb;
                $query = "INSERT INTO Goal (Goal, Due, Upload, Username) VALUES (:goal, :due, :upload, :me);";
                $bindings = array(":goal" => $data["goal"],":due" => $data["due"], ":upload" => date("y/m/d"),":me" => $data["me"]);
                $goal = $db->query($query, $bindings);

                //if add was ok
                if ($goal["count"] > 0) {

                    $GoalID = $db->lastInsertId();
                    $results = getGoal($GoalID);

                    $results["meta"]["ok"] = true;
                    $results["meta"]["status"] = 201;
                    $results["meta"]["message"] = "Created";

                }
                //else error adding goal
                else {

                    //check if database provided any meta data if so problem with executing query
                    if (isset($goal["meta"])) {
                        $results = $goal;
                    }
                    //else couldn't insert for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }

                }

            }
            //else due date provided is valid
            else {
                $results["meta"]["feedback"] = "Due date not valid.";
                $results["meta"]["status"] = 400;
                $results["meta"]["ok"] = false;
            }
        }
        //error getting user
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($data["username"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//edits a goal user has posted before
function editGoal($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("goalID");
    if (checkData($data, $dataNeeded)) {

        //get goal provided
        $goal = getGoal($data["goalID"]);
        if ($goal["count"] > 0) {

            //check if changing completion of goal
            if (isset($data["completion"])) {
                $query = "UPDATE Goal SET Completion = NOT Completion WHERE ID = :goalID;";
                $bindings = array(":goalID" => $data["goalID"]);
            }
            //else check if changing goal of a goal
            else if (isset($data["goal"]) && trim($data["goal"]) != "") {
                $query = "UPDATE Goal SET Goal = :goal WHERE ID = :goalID;";
                $bindings = array(":goal" => $data["goal"], ":goalID" => $data["goalID"]);
            }

            if (isset($query) && isset($bindings)) {

                $db = new pdodb;
                $goal = $db->query($query, $bindings);

                //if update was ok
                if ($goal["count"] > 0) {
                    $results = getGoal($data["goalID"]);
                    $results["meta"]["ok"] = true;
                }
                //error updating goal
                else {
                    //check if database provided any meta data if so problem with executing query
                    if (isset($goal["meta"])) {
                        $results = $goal;
                    }
                    //else couldn't update fo unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }

                }

            }

        }
        //error getting goal
        else {
            //check if database provided any meta data if so problem with executing query
            if (isset($goal["meta"])) {
                $results = $goal;
            }
            //else no goal found
            else {
                $results["meta"] = noGoalFound($data["goalID"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//deletes a goal user has posted before
function deleteGoal($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("goalID");
    if (checkData($data, $dataNeeded)) {

        //error getting goal
        $goal = getGoal($data["goalID"]);
        if ($goal["count"] > 0) {

            $db = new pdodb;
            //delete the comments linked to goal
            $query = "DELETE FROM Comment WHERE GoalID = :goalID;";
            $bindings = array(":goalID" => $data["goalID"]);
            $db->query($query, $bindings);

            //delete likes linked to goal
            $query = "DELETE FROM Liked WHERE GoalID = :goalID;";
            $db->query($query, $bindings);

            //finally delete the actual goal
            $query = "DELETE FROM Goal WHERE ID = :goalID;";
            $goal = $db->query($query, $bindings);

            //if deletion was ok
            if ($goal["count"] > 0) {
                $results["meta"]["ok"] = true;

                $results["rows"]["GoalID"] = $data["goalID"];
            }
            //error deleting goal
            else {
                //check if database provided any meta data if so problem with executing query
                if (isset($goal["meta"])) {
                    $results = $goal;
                }
                //else couldn't delete fo unknown reason
                else {
                    $results["meta"]["ok"] = false;
                }

            }

        }
        //error getting goal
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($goal["meta"])) {
                $results = $goal;
            }
            //else no goal found
            else {
                $results["meta"] = noGoalFound($data["goalID"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//gets the comments for a goal
function getComments($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("goalID");
    if (checkData($data, $dataNeeded)) {

        //get goal provided
        $goal = getGoal($data["goalID"]);
        if ($goal["count"] > 0) {

            $db = new pdodb;
            $query = "SELECT * FROM Comment WHERE GoalID = :goalID ORDER BY Upload, ID;";
            $bindings = array(":goalID" => $data["goalID"]);
            $comments = $db->query($query, $bindings);

            //check if database provided any meta data if so problem with executing query
            if (isset($comments["meta"])) {
                $results = $comments;
            }
            //else return comments
            else {
                $results = $comments;
                $results["meta"]["ok"] = true;
            }

        }
        //error getting goal
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($goal["meta"])) {
                $results = $goal;
            }
            //else no goal found
            else {
                $results["meta"] = noGoalFound($data["goalID"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//add a comment to a goal user has attempted to add
function addComment($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("comment", "goalID", "me");
    if (checkData($data, $dataNeeded)) {

        $goal = getGoal($data["goalID"]);
        if ($goal["count"] > 0) {

            //check if user provided exists
            $user = getAUser($data["me"]);
            if ($user["count"] > 0) {

                $db = new pdodb;
                $query = "INSERT INTO Comment (Comment, GoalID, Username, Upload) VALUES (:comment, :goalID, :me, :upload);";
                $bindings = array(":comment" => $data["comment"], ":goalID" => $data["goalID"], ":me" => $data["me"], ":upload" => date("y/m/d"));
                $comment = $db->query($query, $bindings);

                //if add was ok
                if ($comment["count"] > 0) {
                    $commentID = $db->lastInsertId();
                    $results = getComment($commentID);
                    $results["meta"]["ok"] = true;
                    $results["meta"]["status"] = 201;
                    $results["meta"]["message"] = "Created";
                }
                //error adding comment
                else {
                    //check if database provided any meta data if so problem with executing query
                    if (isset($comment["meta"])) {
                        $results = $comment;
                    }
                    //else error adding for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }

                }

            }
            //error getting user
            else {

                //check if database provided any meta data if so problem with executing query
                if (isset($user["meta"])) {
                    $results = $user;
                }
                //else no user found
                else {
                    $results["meta"] = noUserFound($data["me"]);
                }

            }

        }
        //error getting goal
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($goal["meta"])) {
                $results = $goal;
            }
            //else no goal found
            else {
                $results["meta"] = noGoalFound($data["goalID"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//edit a comment they posted on a goal before
function editComment($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("comment", "commentID");
    if (checkData($data, $dataNeeded)) {

        //get comment provided
        $comment = getComment($data["commentID"]);
        if ($comment["count"] > 0) {

            $db = new pdodb;
            $query = "UPDATE Comment SET Comment = :comment WHERE ID = :commentID;";
            $bindings = array(":comment" => $data["comment"], "commentID" => $data["commentID"]);
            $comment = $db->query($query, $bindings);

            //if update was ok
            if ($comment["count"] > 0) {
                $results = getComment($data["commentID"]);
                $results["meta"]["ok"] = true;
            }
            //else error updating comment
            else {
                //check if database provided any meta data if so problem with executing query
                if (isset($comment["meta"])) {
                    $results = $comment;
                }
                //else no comment found
                else {
                    $results["meta"]["ok"] = false;
                }

            }

        }
        //error getting comment
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($comment["meta"])) {
                $results = $comment;
            }
            //else no comment found
            else {
                $results["meta"] = noCommentFound($data["commentID"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//delete a comment they posted on a goal before
function deleteComment($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("commentID");
    if (checkData($data, $dataNeeded)) {

        //check if comment provided exists
        $comment = getComment($data["commentID"]);
        if ($comment["count"] > 0) {

            $db = new pdodb;
            $query = "DELETE FROM Comment WHERE ID = :commentID;";
            $bindings = array(":commentID" => $data["commentID"]);
            $row = $db->query($query, $bindings);

            //if deletion was ok
            if ($row["count"] > 0) {
                $results["meta"]["ok"] = true;

                $results["rows"]["commentID"] = $data["commentID"];
            }
            //error deleting comment
            else {
                //check if database provided any meta data if so problem with executing query
                if (isset($row["meta"])) {
                    $results = $row;
                }
                //else no comment found
                else {
                    $results["meta"]["ok"] = false;
                }

            }

        }
        //error getting comment
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($comment["meta"])) {
                $results = $comment;
            }
            //else no comment found
            else {
                $results["meta"] = noCommentFound($data["commentID"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//add a user as one of their friends
function addFriend($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("username", "userToFollow");
    if (checkData($data, $dataNeeded)) {

        //check if user provided exists
        $user = getAUser($data["username"]);
        if ($user["count"] > 0) {

            //check if userToFollow provided exists
            $userToFollow = getAUser($data["userToFollow"]);
            if ($userToFollow["count"] > 0) {

                $db = new pdodb;
                $query = "INSERT INTO Follow (Username1, Following) VALUES (:username, :userToFollow);";
                $bindings = array(":username" => $data["username"], ":userToFollow" => $data["userToFollow"]);
                $follow = $db->query($query, $bindings);

                //if add was ok
                if ($follow["count"] > 0) {

                    $query = "SELECT * FROM Follow WHERE Username1 = :username AND Following = :userToFollow;";
                    $follow = $db->query($query, $bindings);

                    $results = $follow;

                    $results["meta"]["ok"] = true;
                    $results["meta"]["status"] = 201;
                    $results["meta"]["message"] = "Created";
                }
                //else error adding follow
                else {

                    //check if database provided any meta data if so problem with executing query
                    if (isset($follow["meta"])) {
                        $results = $follow;
                    }
                    //else couldn't add for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }

                }

            }
            //else error getting userToFollow
            else {

                //check if database provided any meta data if so problem with executing query
                if (isset($userToFollow["meta"])) {
                    $results = $userToFollow;
                }
                //else no user found
                else {
                    $results["meta"] = noUserFound($data["userToFollow"]);
                }

            }

        }
        //else error getting user
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($data["me"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//delete a friend from their friends list
function deleteFriend($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("username", "userFollowing");
    if (checkData($data, $dataNeeded)) {

        //check if user provided exists
        $user = getAUser($data["username"]);
        if ($user["count"] > 0) {

            //check if userFollowing provided exists
            $userFollowing = getAUser($data["userFollowing"]);
            if ($userFollowing["count"] > 0) {

                $db = new pdodb;
                $query = "DELETE FROM Follow WHERE Username1 = :username AND Following = :userFollowing;";
                $bindings = array(":username" => $data["username"], ":userFollowing" => $data["userFollowing"]);
                $unfollow = $db->query($query, $bindings);

                //if deletion was ok
                if ($unfollow["count"] > 0) {
                    $results["meta"]["ok"] = true;

                    $results["rows"]["user"] = $data["username"];
                }
                //error deleting follow
                else {

                    //check if database provided any meta data if so problem with executing query
                    if (isset($unfollow["meta"])) {
                        $results = $unfollow;
                    }
                    //else couldn't remove follow for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }

                }

            }
            //else getting userFollowing
            else {

                //check if database provided any meta data if so problem with executing query
                if (isset($userFollowing["meta"])) {
                    $results = $userFollowing;
                }
                //else no user found
                else {
                    $results["meta"] = noUserFound($data["userToFollow"]);
                }

            }

        }
        //else error getting user
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($data["username"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//add a like to a goal user has liked
function addLike($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("username", "goalID");
    if (checkData($data, $dataNeeded)) {

        //check if user provided exists
        $user = getAUser($data["username"]);
        if ($user["count"] > 0) {

            //check if goal provided exists
            $goal = getGoal($data["goalID"]);
            if ($goal["count"] > 0) {

                $db = new pdodb;
                $query = "INSERT INTO Likes (Username, goalID) VALUES (:username, :goalID);";
                $bindings = array(":username" => $data["username"], ":goalID" => $data["goalID"]);
                $like = $db->query($query, $bindings);

                //if add was ok
                if ($like["count"] > 0) {
                    $query = "SELECT * FROM Likes WHERE Username = :username AND goalID = :goalID;";
                    $like = $db->query($query, $bindings);

                    $results = $like;
                    $results["meta"]["ok"] = true;
                    $results["meta"]["status"] = 201;
                    $results["meta"]["message"] = "Created";
                }
                //error adding like
                else {

                    //check if database provided any meta data if so problem with executing query
                    if (isset($like["meta"])) {
                        $results = $like;
                    }
                    //else couldn't add like for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }

                }

            }
            //error getting goal
            else {

                //check if database provided any meta data if so problem with executing query
                if (isset($goal["meta"])) {
                    $results = $goal;
                }
                //else no goal found
                else {
                    $results["meta"] = noGoalFound($data["goalID"]);
                }

            }

        }
        //error getting user
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($data["username"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//delete a like of a goal user liked before
function deleteLike($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("username", "goalID");
    if (checkData($data, $dataNeeded)) {

        //check if user provided exists
        $user = getAUser($data["username"]);
        if ($user["count"] > 0) {

            //check if goal provided exists
            $goal = getGoal($data["goalID"]);
            if ($goal["count"] > 0) {

                //delete from database
                $db = new pdodb;
                $query = "DELETE FROM Likes WHERE Username = :username AND GoalID = :goalID;";
                $bindings = array(":username" => $data["username"], ":goalID" =>$data["goalID"]);
                $unlike = $db->query($query, $bindings);

                //if deletion was ok
                if ($unlike["count"] > 0) {
                    $results["meta"]["ok"] = true;

                    //return GoalID to know whats been deleted on js/ui side of things
                    $results["rows"]["goalID"] = $data["goalID"];
                }
                //error with deletion
                else {

                    //check if database provided any meta data if so problem with executing query
                    if (isset($unlike["meta"])) {
                        $results = $unlike;
                    }
                    //else couldn't delete like for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }

                }

            }
            //else error getting goal
            else {

                //check if database provided any meta data if so problem with executing query
                if (isset($goal["meta"])) {
                    $results = $goal;
                }
                //else no goal found
                else {
                    $results["meta"] = noGoalFound($data["goalID"]);
                }

            }

        }
        //error getting user
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($data["username"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}

//upload a picture a user has uploaded as their profile picture
function addPicture($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("username");
    if (checkData($data, $dataNeeded) && isset($_FILES["picture"])) {

        $username = $data["username"];

        //check if user provided exists
        $user = getAUser($username);
        if ($user["count"] > 0) {

            //get the file type
            $imageFileType = pathinfo(basename($_FILES["picture"]["name"]), PATHINFO_EXTENSION);
            
            //the directory to upload file
            $directory = "../../images/uploads/";

            //the full path for new file
            $fileLocation = $directory . $username . "." . $imageFileType;

            //check if file is a actual image
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($fileInfo, $_FILES["picture"]["tmp_name"]);
            finfo_close($fileInfo);
            if ((strpos($fileType, 'image/') !== false)) {

                //try to upload file
                if (move_uploaded_file($_FILES["picture"]["tmp_name"], $fileLocation)) {

                    //update database with location of new picture
                    $db = new pdodb;
                    $query = "UPDATE User SET Picture = :file WHERE Username = :username;";
                    $bindings = array(":file" => $fileLocation, ":username" => $username);
                    $picture = $db->query($query, $bindings);

                    //if update of user was ok
                    if ($picture["count"] > 0) {

                        $results = getAUser($data["username"]);

                        $results["meta"]["ok"] = true;
                        $results["meta"]["status"] = 201;
                        $results["meta"]["message"] = "Created";
                    }
                    //else error updating user
                    else {

                        //check if database provided any meta data if so problem with executing query
                        if (isset($picture["meta"])) {
                            $results = $picture;
                        }
                        //else couldn't update picture on database for unknown reason
                        else {
                            $results["meta"]["ok"] = false;
                        }

                    }

                }
                //else problem uploading file to server
                else {
                    $results["meta"]["feedback"] = "Sorry, there was an error uploading your file.";
                }

            }
            //else bad request as file uploaded is not a image
            else {
                $results["meta"]["status"] = 400;
                $results["meta"]["message"] = "Bad Request";
                $results["meta"]["feedback"] = "File is not an image.";
            }

        }
        //else problem getting user
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($data["username"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    $results["meta"]["files"] = $_FILES;

    return $results;
}

//deletes a profile picture user uploaded before
function deletePicture($data)
{
    $results = [];

    //checks if requests needed are present and not empty
    $dataNeeded = array("username");
    if (checkData($data, $dataNeeded)) {

        //check if user provided exists
        $user = getAUser($data["username"]);
        if ($user["count"] > 0) {

            //update database
            $db = new pdodb;
            $query = "UPDATE User SET Picture = null WHERE Username = :username;";
            $bindings = array(":username" => $data["username"]);
            $picture = $db->query($query, $bindings);

            //if update was ok
            if ($picture["count"] > 0) {

                //checks if file exists to delete the picture
                if (file_exists($user["rows"][0]["Picture"])) {
                    unlink($user["rows"][0]["Picture"]);
                }

                //return updated user
                $results = getAUser($data["username"]);
                $results["meta"]["ok"] = true;

            }
            //else error updating
            else {

                //check if database provided any meta data if so problem with executing query
                if (isset($picture["meta"])) {
                    $results = $picture;
                }
                //else couldn't update for unknown reason
                else {
                    $results["meta"]["ok"] = false;
                }

            }

        }
        //else problem getting user
        else {

            //check if database provided any meta data if so problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($data["username"]);
            }

        }

    }
    //else data was not provided
    else {
        $results["meta"] = dataNotProvided($dataNeeded);
    }

    return $results;
}