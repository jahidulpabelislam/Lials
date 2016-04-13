<?php
/*
 * All the functions for API
 * @author 733474
*/

//contains the different functions for the API

/**
 * Check if all request data needed is provided
 * And requests provided is valid
 * @param $requests array array of requests provided
 * @param $requestsNeeded array array of requests needed
 * @return bool whether requests provided is valid and request data needed is provided
 */
function checkRequestsPresent($requests, $requestsNeeded)
{
    //loops through each request needed
    foreach ($requestsNeeded as $request) {
        //checks if request needed is provided and is not empty
        if (!isset($requests[$request]) || trim($requests[$request]) == "") {
            //return false as request needed is not provided or empty
            return false;
        }
    }
    //otherwise requests provided are ok and requests needed are provided
    return true;
}

/**
 * When the method provided is not allowed
 * @return array array of meta data
 */
function methodNotAllowed()
{
    $meta["ok"] = false;
    $meta["status"] = 405;
    $meta["message"] = "Method not allowed.";
    $meta["feedback"] = "Method Not Allowed.";
    return $meta;
}

/**
 * Send necessary meta data back when needed data is not provided
 * @param $requestsNeeded array array of requests needed
 * @return array array of meta data
 */
function requestsNotProvided($requestsNeeded)
{
    $meta["ok"] = false;
    $meta["status"] = 400;
    $meta["message"] = "Bad Request";
    $meta["requestsNeeded"] = $requestsNeeded;
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

//send necessary back data when data provided is not correct
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

//send necessary back data when data provided is not correct
function noGoalFound($goalID)
{
    $meta["ok"] = false;
    $meta["status"] = 404;
    $meta["feedback"] = "No goal found with ${goalID} as ID.";
    $meta["message"] = "Not Found";
    return $meta;
}

//get a particular goal defined by $goalID
function getComment($commentID)
{
    $db = new pdodb;
    $query = "SELECT * FROM Comment WHERE ID = :commentID;";
    $bindings = array('commentID' => $commentID);
    return $comment = $db->query($query, $bindings);
}

//send necessary back data when data provided is not correct
function noCommentFound($commentID)
{
    $meta["ok"] = false;
    $meta["status"] = 404;
    $meta["feedback"] = "No Comment found with ${commentID} as ID.";
    $meta["message"] = "Not Found";
    return $meta;
}

//gets a user, either trying to log in or trying to get information of a user
function getUser($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("username");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //checks if user exists
        $user = getAUser($requests["username"]);
        if (count($user["rows"]) > 0) {
            $db = new pdodb;
            //checks if includes a password which means user is trying to log in
            if (isset($requests["password"])) {
                //get the user with username and password provided
                $query = "SELECT Username FROM User WHERE Username = :username AND Password = :password;";
                $bindings = array(":username" => $requests["username"], ":password" => $requests["password"]);
                $user = $db->query($query, $bindings);
                //checks if user gave correct password
                if (count($user["rows"]) > 0) {
                    $results["meta"]["ok"] = true;
                    $results["rows"] = $user["rows"];
                }
                //else something gone wrong
                else {
                    //check if database provided any meta data if so query problem with executing query
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
            //else checks if "me" was provided which means user is trying to get a another user
            else if (isset($requests["me"])) {
                $user = getAUser($requests["me"]);
                if (count($user["rows"]) > 0) {
                    //gets the user
                    $query = "SELECT Username, Picture, Private, Username1 FROM User LEFT JOIN (SELECT * FROM Following WHERE Username1 = :me) AS u ON Username = Username2 WHERE Username = :username;";
                    $bindings = array(":me" => $requests["me"], ":username" => $requests["username"]);
                    $user = $db->query($query, $bindings);
                    $results["meta"]["ok"] = true;
                    $results["rows"] = $user["rows"];
                }
                else {
                    //check if database provided any meta data if so query problem with executing query
                    if (isset($user["meta"])) {
                        $results = $user;
                    }
                    //else no user found
                    else {
                        $results["meta"] = noUserFound($requests["me"]);
                    }
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($requests["username"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//add a new user a user has attempted to add
function addUser($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("username", "password");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //checks if user already exists with username
        $user = getAUser($requests["username"]);
        if (count($user["rows"]) == 0) {
            $db = new pdodb;
            $query = "INSERT INTO User (Username, Password) VALUES (:username,:password);";
            $bindings = array(":username" => $requests["username"], ":password" => $requests["password"]);
            $user = $db->query($query, $bindings);

            //if add was ok
            if ($user["count"] > 0) {
                $results["meta"]["ok"] = true;
                $results["meta"]["status"] = 201;
                $results["meta"]["message"] = "Created";

                $user = getAUser($requests["username"]);

                $results["rows"] = $user["rows"];
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($user["meta"])) {
                    $results = $user;
                }
                //else couldn't add user for unknown reason
                else {
                    $results["meta"]["ok"] = false;
                }
            }
        } else {
            $results["meta"]["ok"] = false;
            $results["meta"]["status"] = 409;
            $results["meta"]["feedback"] = "${requests["username"]} Already Taken.";
            $results["meta"]["message"] = "Conflict";
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//edits a user private attribute
function editUser($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("username");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //checks if user provided exists
        $user = getAUser($requests["username"]);
        if (count($user["rows"]) > 0) {
            $db = new pdodb;
            $query = "UPDATE User SET Private = NOT Private WHERE Username = :username;";
            $bindings = array(":username" => $requests["username"]);
            $user = $db->query($query, $bindings);

            //if update was ok
            if ($user["count"] > 0) {
                $results["meta"]["ok"] = true;

                $user = getAUser($requests["username"]);

                $results["rows"] = $user["rows"];
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($user["meta"])) {
                    $results = $user;
                }
                //else couldn't edit user for unknown reason
                else {
                    $results["meta"]["ok"] = false;
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($requests["username"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//gets goals , either of a user or filtered by a search or goals of friends user is following plus theirs
function getGoals($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("me");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //checks if user provided exists
        $user = getAUser($requests["me"]);
        if (count($user["rows"]) > 0) {
            $bindings = array();
            $following = "";
            //if it includes a username of a user to get goals of
            if (isset($requests["user"]) && trim($requests["user"]) != "") {
                $user = getAUser($requests["user"]);
                if (count($user["rows"]) > 0) {
                    $where = "Goal.Username = :user";
                    $bindings[":user"] = $requests["user"];
                } else {
                    //check if database provided any meta data if so query problem with executing query
                    if (isset($user["meta"])) {
                        $results = $user;
                    }
                    //else no user found
                    else {
                        $results["meta"] = noUserFound($requests["user"]);
                    }
                }
            }
            //if it includes a search
            else if (isset($requests["search"]) && trim($requests["search"]) != "") {
                //split each word in search
                $searches = explode(" ", $requests["search"]);
                //check if there's a 'NOT' in search
                $not = array_search("NOT", $searches, true);
                //check if there's a "OR" in search
                $or = array_search("OR", $searches, true);
                //if using a NOT in query
                if (($not !== false) && ($not > 0) && ($not < count($searches) - 1)) {
                    $not1 = $not2 = "%";
                    foreach ($searches as $aSearch) {
                        $aSearchPosition = array_search($aSearch, $searches, true);
                        if ($aSearchPosition < $not) {
                            $not1 .= "${aSearch}%";
                        } else if ($aSearchPosition > $not) {
                            $not2 .= "${aSearch}%";
                        }
                        unset($searches[$aSearchPosition]);
                    }
                    $where = "Goal LIKE :not1 AND Goal NOT LIKE :not2";
                    $bindings[":not1"] = $not1;
                    $bindings[":not2"] = $not2;
                }
                //if using a OR in query
                else if ($or !== false && $or > 0 && $or < count($searches) - 1) {
                    $or1 =  $or2 = "%";
                    foreach ($searches as $aSearch) {
                        $aSearchPosition = array_search($aSearch, $searches, true);
                        if ($aSearchPosition < $or) {
                            $or1 .= "${aSearch}%";
                        } else if ($aSearchPosition > $or) {
                            $or2 .= "${aSearch}%";
                        }
                        unset($searches[$aSearchPosition]);
                    }
                    $where = "Goal LIKE :or1 OR Goal LIKE :or2";
                    $bindings[":or1"] = $or1;
                    $bindings[":or2"] = $or2;
                }
                //else assume search is for a general search
                else {
                    $search = "%";
                    foreach ($searches as $aSearch) {
                        $search .= "${aSearch}%";
                    }
                    $where = "Goal LIKE :search";
                    $bindings[":search"] = $search;
                }
            }
            //if not search or username provided assumes goals of users they are following
            else {
                $following = "LEFT JOIN (SELECT * FROM Following WHERE Username1 = :me) AS gf ON Goal.Username = Username2";
                $where = "Username1 IS NOT NULL OR Goal.Username = :me";
            }
            if (isset($where)) {
                $db = new pdodb;
                $query = "SELECT * FROM Goal ${following} LEFT JOIN (SELECT GoalID FROM Liked WHERE Username = :me) AS gl ON ID = GoalID WHERE ${where} ORDER BY Upload, ID;";
                $bindings[":me"] = $requests["me"];
                $goals = $db->query($query, $bindings);
                //check if database provided any meta data if so query problem with executing query
                if (isset($goals["meta"])) {
                    $results = $goals;
                }
                //else query was alright so return data
                else {
                    $results["meta"]["ok"] = true;
                    $results["rows"] = $goals["rows"];
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($requests["me"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//add a goal user has attempted to add
function addGoal($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("goal", "due", "me");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //check if user provided exists
        $user = getAUser($requests["me"]);
        if (count($user["rows"]) > 0) {
            //checks if email provided is valid using REGEX
            if (preg_match("/\b[\d]{4}-[\d]{2}-[\d]{2}\b/im", $requests["due"])) {
                $db = new pdodb;
                $query = "INSERT INTO Goal (Goal, Due, Upload, Username) VALUES (:goal, :due, :upload, :me);";
                $bindings = array(":goal" => $requests["goal"],":due" => $requests["due"], ":upload" => date("y/m/d"),":me" => $requests["me"]);
                $goal = $db->query($query, $bindings);

                //if add was ok
                if ($goal["count"] > 0) {
                    $results["meta"]["ok"] = true;
                    $results["meta"]["status"] = 201;
                    $results["meta"]["message"] = "Created";

                    $GoalID = $db->lastInsertId();

                    $goal = getGoal($GoalID);
                    $results["rows"] = $goal["rows"];
                } else {
                    //check if database provided any meta data if so query problem with executing query
                    if (isset($goal["meta"])) {
                        $results = $goal;
                    }
                    //else couldn't insert for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }
                }
            } else {
                $results["meta"]["feedback"] = "Due date not valid.";
                $results["meta"]["status"] = 400;
                $results["meta"]["ok"] = false;
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($requests["username"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//edits a goal user has posted before
function editGoal($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("goalID");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $goal = getGoal($requests["goalID"]);
        if (count($goal["rows"]) > 0) {
            $db = new pdodb;
            if (isset($requests["completion"])) {
                $query = "UPDATE Goal SET Completion = NOT Completion WHERE ID = :goalID;";
                $bindings = array(":goalID" => $requests["goalID"]);
            } else if (isset($requests["goal"]) && trim($requests["goal"]) != "") {
                $query = "UPDATE Goal SET Goal = :goal WHERE ID = :goalID;";
                $bindings = array(":goal" => $requests["goal"], ":goalID" => $requests["goalID"]);
            }
            if (isset($query) && isset($bindings)) {
                $goal = $db->query($query, $bindings);
                //if update was ok
                if ($goal["count"] > 0) {
                    $results["meta"]["ok"] = true;

                    $goal = getGoal($requests["goalID"]);
                    $results["rows"] = $goal["rows"];
                } else {
                    //check if database provided any meta data if so query problem with executing query
                    if (isset($goal["meta"])) {
                        $results = $goal;
                    }
                    //else couldn't update fo unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($goal["meta"])) {
                $results = $goal;
            }
            //else no goal found
            else {
                $results["meta"] = noGoalFound($requests["goalID"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//deletes a goal user has posted before
function deleteGoal($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("goalID");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $goal = getGoal($requests["goalID"]);
        if (count($goal["rows"]) > 0) {
            $db = new pdodb;
            //delete the comments linked to goal
            $query = "DELETE FROM Comment WHERE GoalID = :goalID;";
            $bindings = array(":goalID" => $requests["goalID"]);
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

                $results["rows"]["GoalID"] = $requests["goalID"];
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($goal["meta"])) {
                    $results = $goal;
                }
                //else couldn't delete fo unknown reason
                else {
                    $results["meta"]["ok"] = false;
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($goal["meta"])) {
                $results = $goal;
            }
            //else no goal found
            else {
                $results["meta"] = noGoalFound($requests["goalID"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//gets the comments for a goal
function getComments($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("goalID");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $goal = getGoal($requests["goalID"]);
        if (count($goal["rows"]) > 0) {
            $db = new pdodb;
            $query = "SELECT * FROM Comment WHERE GoalID = :goalID ORDER BY Upload, ID;";
            $bindings = array(":goalID" => $requests["goalID"]);
            $comments = $db->query($query, $bindings);

            //check if database provided any meta data if so query problem with executing query
            if (isset($comments["meta"])) {
                $results = $comments;
            }
            //else return comments
            else {
                $results["meta"]["ok"] = true;
                $results["rows"] = $comments["rows"];
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($goal["meta"])) {
                $results = $goal;
            }
            //else no goal found
            else {
                $results["meta"] = noGoalFound($requests["goalID"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//add a comment to a goal user has attempted to add
function addComment($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("comment", "goalID", "me");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $goal = getGoal($requests["goalID"]);
        if (count($goal["rows"]) > 0) {
            //check if user provided exists
            $user = getAUser($requests["me"]);
            if (count($user["rows"]) > 0) {
                $db = new pdodb;
                $query = "INSERT INTO Comment (Comment, GoalID, Username, Upload) VALUES (:comment, :goalID, :me, :upload);";
                $bindings = array(":comment" => $requests["comment"], ":goalID" => $requests["goalID"], ":me" => $requests["me"], ":upload" => date("y/m/d"));
                $comment = $db->query($query, $bindings);

                //if add was ok
                if ($comment["count"] > 0) {
                    $results["meta"]["ok"] = true;
                    $results["meta"]["status"] = 201;
                    $results["meta"]["message"] = "Created";

                    $commentID = $db->lastInsertId();

                    $comment = getComment($commentID);

                    $results["rows"] = $comment["rows"];
                } else {
                    //check if database provided any meta data if so query problem with executing query
                    if (isset($comment["meta"])) {
                        $results = $comment;
                    }
                    //else error addinng for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }
                }
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($user["meta"])) {
                    $results = $user;
                }
                //else no user found
                else {
                    $results["meta"] = noUserFound($requests["me"]);
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($goal["meta"])) {
                $results = $goal;
            }
            //else no goal found
            else {
                $results["meta"] = noGoalFound($requests["goalID"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//edit a comment they posted on a goal before
function editComment($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("comment", "commentID");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $comment = getComment($requests["commentID"]);
        if (count($comment["rows"]) > 0) {
            $db = new pdodb;
            $query = "UPDATE Comment SET Comment = :comment WHERE ID = :commentID;";
            $bindings = array(":comment" => $requests["comment"], "commentID" => $requests["commentID"]);
            $comment = $db->query($query, $bindings);

            //if update was ok
            if ($comment["count"] > 0) {
                $results["meta"]["ok"] = true;

                $comment = getComment($requests["commentID"]);

                $results["rows"] = $comment["rows"];
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($comment["meta"])) {
                    $results = $comment;
                }
                //else no comment found
                else {
                    $results["meta"]["ok"] = false;
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($comment["meta"])) {
                $results = $comment;
            }
            //else no comment found
            else {
                $results["meta"] = noCommentFound($requests["commentID"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//delete a comment they posted on a goal before
function deleteComment($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("commentID");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //check if comment provided exists
        $row = getComment($requests["commentID"]);
        if (count($row["rows"]) > 0) {
            $db = new pdodb;
            $query = "DELETE FROM Comment WHERE ID = :commentID;";
            $bindings = array(":commentID" => $requests["commentID"]);
            $row = $db->query($query, $bindings);

            //if deletion was ok
            if ($row["count"] > 0) {
                $results["meta"]["ok"] = true;

                $results["rows"]["commentID"] = $requests["commentID"];
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($row["meta"])) {
                    $results = $row;
                }
                //else no comment found
                else {
                    $results["meta"]["ok"] = false;
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($row["meta"])) {
                $results = $row;
            }
            //else no comment found
            else {
                $results["meta"] = noCommentFound($requests["commentID"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//add a user as one of their friends
function addFriend($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("me", "user");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //check if user provided exists
        $user = getAUser($requests["me"]);
        if (count($user["rows"]) > 0) {
            //check if user provided exists
            $user = getAUser($requests["user"]);
            if (count($user["rows"]) > 0) {
                $db = new pdodb;
                $query = "INSERT INTO Following (Username1, Username2) VALUES (:me, :user);";
                $bindings = array(":me" => $requests["me"], ":user" => $requests["user"]);
                $follow = $db->query($query, $bindings);

                //if add was ok
                if ($follow["count"] > 0) {
                    $results["meta"]["ok"] = true;
                    $results["meta"]["status"] = 201;
                    $results["meta"]["message"] = "Created";

                    $query = "SELECT * FROM Following WHERE Username1 = :me AND Username2 = :user;";
                    $follow = $db->query($query, $bindings);

                    $results["rows"] = $follow["rows"];
                } else {
                    //check if database provided any meta data if so query problem with executing query
                    if (isset($follow["meta"])) {
                        $results = $follow;
                    }
                    //else couldn't add for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }
                }
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($user["meta"])) {
                    $results = $user;
                }
                //else no user found
                else {
                    $results["meta"] = noUserFound($requests["user"]);
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($requests["me"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }

    return $results;
}

//delete a friend from their friends list
function deleteFriend($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("me", "user");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //check if user provided exists
        $user = getAUser($requests["me"]);
        if (count($user["rows"]) > 0) {
            //check if user provided exists
            $user = getAUser($requests["user"]);
            if (count($user["rows"]) > 0) {
                $db = new pdodb;
                $query = "DELETE FROM Following WHERE Username1 = :me AND Username2 = :user;";
                $bindings = array(":me" => $requests["me"], ":user" => $requests["user"]);
                $unfollow = $db->query($query, $bindings);

                //if deletion was ok
                if ($unfollow["count"] > 0) {
                    $results["meta"]["ok"] = true;

                    $results["rows"]["user"] = $requests["user"];
                } else {
                    //check if database provided any meta data if so query problem with executing query
                    if (isset($unfollow["meta"])) {
                        $results = $unfollow;
                    }
                    //else couldn't remove follow for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }
                }
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($user["meta"])) {
                    $results = $user;
                }
                //else no user found
                else {
                    $results["meta"] = noUserFound($requests["user"]);
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($requests["me"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//add a like to a goal user has liked
function addLike($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("me", "goalID");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //check if user provided exists
        $user = getAUser($requests["me"]);
        if (count($user["rows"]) > 0) {
            //check if goal provided exists
            $goal = getGoal($requests["goalID"]);
            if (count($goal["rows"]) > 0) {
                $db = new pdodb;
                $query = "INSERT INTO Liked (Username, goalID) VALUES (:me, :goalID);";
                $bindings = array(":me" => $requests["me"], ":goalID" => $requests["goalID"]);
                $like = $db->query($query, $bindings);

                //if add was ok
                if ($like["count"] > 0) {
                    $results["meta"]["ok"] = true;
                    $results["meta"]["status"] = 201;
                    $results["meta"]["message"] = "Created";

                    $query = "SELECT * FROM Liked WHERE Username = :me AND goalID = :goalID;";
                    $like = $db->query($query, $bindings);

                    $results["rows"] = $like["rows"];
                } else {
                    //check if database provided any meta data if so query problem with executing query
                    if (isset($like["meta"])) {
                        $results = $like;
                    }
                    //else couldn't add like for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }
                }
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($goal["meta"])) {
                    $results = $goal;
                }
                //else no goal found
                else {
                    $results["meta"] = noGoalFound($requests["goalID"]);
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($requests["me"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//delete a like of a goal user liked before
function deleteLike($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("me", "goalID");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //check if user provided exists
        $user = getAUser($requests["me"]);
        if (count($user["rows"]) > 0) {
            $goal = getGoal($requests["goalID"]);
            if (count($goal["rows"]) > 0) {
                $db = new pdodb;
                $query = "DELETE FROM Liked WHERE Username = :me AND GoalID = :goalID;";
                $bindings = array(":me" => $requests["me"], ":goalID" =>$requests["goalID"]);
                $unlike = $db->query($query, $bindings);

                //if deletion was ok
                if ($unlike["count"] > 0) {
                    $results["meta"]["ok"] = true;

                    $results["rows"]["goalID"] = $requests["goalID"];
                } else {
                    //check if database provided any meta data if so query problem with executing query
                    if (isset($unlike["meta"])) {
                        $results = $unlike;
                    }
                    //else couldn't delete like for unknown reason
                    else {
                        $results["meta"]["ok"] = false;
                    }
                }
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($goal["meta"])) {
                    $results = $goal;
                }
                //else no goal found
                else {
                    $results["meta"] = noGoalFound($requests["goalID"]);
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($requests["me"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

//upload a picture a user has uploaded as their profile picture
function addPicture($requests)
{
    $results = [];

    //checks if requests needed are present and not empty
    $requestsNeeded = array("username");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $username = $requests["username"];
        //check if user provided exists
        $user = getAUser($username);
        if (count($user["rows"]) > 0) {
            //get the file type
            $imageFileType = pathinfo(basename($_FILES["picture"]["name"]), PATHINFO_EXTENSION);
            
            //the directory to upload file
            $directory = "../../images/uploads/";
            //the full path for new file
            $file = $directory . $username . "." . $imageFileType;

            //check if image file is a actual image
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($fileInfo, $_FILES["picture"]["tmp_name"]);
            finfo_close($fileInfo);
            if ((strpos($fileType, 'image/') !== false)) {
                //try to upload file
                if (move_uploaded_file($_FILES["picture"]["tmp_name"], $file)) {
                    $db = new pdodb;
                    $query = "UPDATE User SET Picture = :file WHERE Username = :username;";
                    $bindings = array(":file" => $file, ":username" => $username);
                    $picture = $db->query($query, $bindings);
                    $results["meta"]["picture"] = $picture;
                    //if upload was ok
                    if ($picture["count"] > 0) {
                        $results["meta"]["ok"] = true;
                        $results["meta"]["status"] = 201;
                        $results["meta"]["message"] = "Created";

                        $user = getAUser($requests["username"]);
                        $results["rows"] = $user["rows"];
                    } else {
                        //check if database provided any meta data if so query problem with executing query
                        if (isset($user["meta"])) {
                            $results = $user;
                        }
                        //else couldn't update picture on database for unknown reason
                        else {
                            $results["meta"]["ok"] = false;
                        }
                    }
                } else {
                    $results["meta"]["feedback"] = "Sorry, there was an error uploading your file.";
                }
            } else {
                $results["meta"]["feedback"] = "File is not an image.";
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($requests["username"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    $results["meta"]["files"] = $_FILES;
    return $results;
}

//deletes a profile picture user uploaded before
function deletePicture($requests)
{
    $results = [];
    //checks if requests needed are present and not empty
    $requestsNeeded = array("username");
    if (checkRequestsPresent($requests, $requestsNeeded)) {
        //check if user provided exists
        $user = getAUser($requests["username"]);
        if (count($user["rows"]) > 0) {
            //checks if file exists to delete the picture
            if (file_exists($user["rows"][0]["Picture"])) {
                unlink($user["rows"][0]["Picture"]);
            }
            $db = new pdodb;
            $query = "UPDATE User SET Picture = null WHERE Username = :username;";
            $bindings = array(":username" => $requests["username"]);
            $picture = $db->query($query, $bindings);

            //if update was ok
            if ($picture["count"] > 0) {
                $results["meta"]["ok"] = true;

                $user = getAUser($requests["username"]);
                $results["rows"] = $user["rows"];
            } else {
                //check if database provided any meta data if so query problem with executing query
                if (isset($picture["meta"])) {
                    $results = $picture;
                }
                //else couldn't update picture on database fro unknown reason
                else {
                    $results["meta"]["ok"] = false;
                }
            }
        } else {
            //check if database provided any meta data if so query problem with executing query
            if (isset($user["meta"])) {
                $results = $user;
            }
            //else no user found
            else {
                $results["meta"] = noUserFound($requests["username"]);
            }
        }
    } else {
        $results["meta"] = requestsNotProvided($requestsNeeded);
    }
    return $results;
}