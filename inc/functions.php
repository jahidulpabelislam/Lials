<?php
//contains the different functions for the API

//check if all request data needed is provided
function checkRequestsPresent($requests, $requestsNeeded)
{
    //loops through each request needed
    foreach ($requestsNeeded as $request) {
        //checks if request needed is provided
        if (!isset($requests[$request])) {
            return false;
        }
    }
    //loops through each request provided
    foreach ($requests as $request) {
        //checks if request provided is empty
        if (trim($request) == "") {
            return false;
        }
    }
    return true;
}

//when the method provided is not allowed
function methodNotAllowed()
{
    $results["meta"]["feedback"] = "Method not allowed.";
    $results["meta"]["status"] = 404;
    $results["meta"]["ok"] = false;
    return $results;
}

//send necessary back data when needed data is not provided
function requestsNotProvided($requestsNeeded)
{
    $results["meta"]["ok"] = false;
    $results["meta"]["status"] = 400;
    $results["meta"]["message"] = "Bad Request";
    $results["meta"]["requestsNeeded"] = $requestsNeeded;
    $results["meta"]["feedback"] = "The necessary data was not provided.";
    return $results;
}

//send necessary back data when data provided is not correct
function noUserFound($username)
{
    $meta["ok"] = false;
    $meta["status"] = 404;
    $meta['feedback'] = "No user found with ${username} as username.";
    $meta["message"] = "Not Found";
    return $meta;
}

//send necessary back data when data provided is not correct
function noGoalFound($goalID)
{
    $meta["ok"] = false;
    $meta["status"] = 404;
    $meta['feedback'] = "No goal found with ${goalID} as ID.";
    $meta["message"] = "Not Found";
    return $meta;
}

//get a particular goal defined by $goalID
function getGoal($goalID)
{
    $db = new pdodb;
    $query = "SELECT * FROM Goal WHERE ID = ${goalID};";
    return $db->query($query);
}

//send necessary back data when data provided is not correct
function noCommentFound($commentID)
{
    $meta["ok"] = false;
    $meta["status"] = 404;
    $meta['feedback'] = "No Comment found with ${commentID} as ID.";
    $meta["message"] = "Not Found";
    return $meta;
}

//get a particular goal defined by $goalID
function getComment($commentID)
{
    $db = new pdodb;
    $query = "SELECT * FROM Comment WHERE ID = ${commentID};";
    return $db->query($query);
}

//get a user
function getUser($requests)
{
    $results = [];

    $requestsNeeded = array("username");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $db = new pdodb;
        $query = "SELECT * FROM User WHERE Username = ?;";
        $bindings = array($requests["username"]);
        $row = $db->query($query, $bindings);
        if (count($row) > 0) {
            if (isset($requests['password'])) {
                $query = "SELECT Username FROM User WHERE Username = ? AND Password = ?;";
                $bindings = array($requests["username"], $requests['password']);
                $user = $db->query($query, $bindings);
                if (count($user) == 0) {
                    $results["meta"]["ok"] = false;
                    $results["meta"]["status"] = 401;
                    $results["meta"]["message"] = "Unauthorized";
                    $results['meta']['feedback'] = "Password is wrong please try again.";
                } else {
                    $results["meta"]["ok"] = true;
                    $results["rows"] = $user;
                }
            } else if (isset($requests['me'])) {
                $query = "SELECT * FROM User WHERE Username = ?;";
                $bindings = array($requests["me"]);
                $row = $db->query($query, $bindings);
                if (count($row) > 0) {
                    $query = "SELECT * FROM User LEFT JOIN (SELECT * FROM Following WHERE Username1 = ?) AS gf ON Username = Username2 WHERE Username = ?;";
                    $bindings = array($requests["me"], $requests['username']);
                    $user = $db->query($query, $bindings);

                    $results["meta"]["ok"] = true;
                    $results["rows"] = $user;
                } else {
                    $results["meta"] = noUserFound($requests['username']);
                }
            }
        } else {
            $results["meta"] = noUserFound($requests['username']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function addUser($requests)
{
    $results = [];

    $requestsNeeded = array("username", "password");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getUser(array("me" => $requests["username"], "username" => $requests["username"]));
        if (count($row["rows"]) == 0) {
            $db = new pdodb;
            $query = "INSERT INTO User (Username, Password) VALUES (?,?);";
            $bindings = array($requests["username"], $requests["password"]);
            $user = $db->query($query, $bindings);

            //if add was ok
            if ($user > 0) {
                $results['meta']["ok"] = true;
                $results['meta']["status"] = 201;
                $results['meta']["message"] = "Created";

                $query = "SELECT Username FROM User WHERE Username = ?;";
                $bindings = array($requests["username"]);
                $user = $db->query($query, $bindings);

                $results["rows"] = $user;
            }
        } elseif (count($row) > 0) {
            $results["meta"]["ok"] = false;
            $results["meta"]["status"] = 409;
            $results['meta']['feedback'] = "Username Already Taken.";
            $results["meta"]["message"] = "Conflict";
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function editUser($requests)
{
    $results = [];

    $requestsNeeded = array("username");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getUser(array("me" => $requests["username"], "username" => $requests["username"]));
        if (count($row["rows"]) > 0) {
            $db = new pdodb;
            $query = "UPDATE User SET Private = NOT Private WHERE Username = ?;";
            $bindings = array($requests["username"]);
            $user = $db->query($query, $bindings);

            //if update was ok
            if ($user > 0) {
                $results["meta"]["ok"] = true;

                $query = "SELECT Private FROM User WHERE Username = ?;";
                $bindings = array($requests["username"]);
                $user = $db->query($query, $bindings);

                $results["rows"] = $user;
            }
        } else {
            $results["meta"] = noUserFound($requests['username']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function getGoals($requests)
{
    $results = [];

    $requestsNeeded = array("me");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $me = $requests['me'];
        $row = getUser(array("me" => $me, "username" => $me));
        if (count($row["rows"]) > 0) {
            $following = "";
            //if it includes a username of a user to get goals of
            if (isset($requests['user'])) {
                $row = getUser(array("me" => $requests['user'], "username" => $requests['user']));
                if (count($row["rows"]) > 0) {
                    $where = "Goal.Username = '${requests['user']}'";
                } else {
                    $results["meta"] = noUserFound($me);
                }
            } else if (isset($requests['search'])) { //if it includes a search
                $searches = $requests['search'];
                $search = "";
                $searches = explode(" ", $searches);
                $not = array_search("NOT", $searches, true);
                $or = array_search('OR', $searches, true);
                //if using a NOT in query
                if (($not !== false) && ($not > 0) && ($not < count($searches) - 1)) {
                    $not1 = "";
                    $not2 = "";
                    foreach ($searches as $aSearch) {
                        if (array_search($aSearch, $searches, true) < $not) {
                            $not1 .= "${aSearch}%";
                        } else if (array_search($aSearch, $searches, true) > $not) {
                            $not2 .= "${aSearch}%";
                        }
                    }
                    $where = "Goal LIKE '%${not1}' AND Goal NOT LIKE '%${not2}'";
                } else if ($or !== false && $or > 0 && $or < count($searches) - 1) { //if using a OR in query
                    $or1 = "";
                    $or2 = "";
                    foreach ($searches as $aSearch) {
                        if (array_search($aSearch, $searches, true) < $or) {
                            $or1 .= "${aSearch}%";
                        } else if (array_search($aSearch, $searches, true) > $or) {
                            $or2 .= "${aSearch}%";
                        }
                    }
                    $where = "Goal LIKE '%${or1}' OR Goal LIKE '%${or2}'";
                } else {
                    foreach ($searches as $aSearch) {
                        $search .= "${aSearch}%";
                    }
                    $where = "Goal LIKE '%${search}'";
                }
            } else { //if not search or username provided assumes goals of users they are following
                $following = "LEFT JOIN (SELECT * FROM Following WHERE Username1 = '${me}') AS gf ON Goal.Username = Username2";
                $where = "Username1 IS NOT NULL OR Goal.Username = '${me}'";
            }
            $db = new pdodb;
            $query = "SELECT * FROM Goal ${following} LEFT JOIN (SELECT GoalID FROM Liked WHERE Username = '${me}') AS gl ON ID = GoalID WHERE ${where} ORDER BY Upload, ID;";
            $goals = $db->query($query);

            $results["meta"]["ok"] = true;
            $results["rows"] = $goals;
        } else {
            $results["meta"] = noUserFound($me);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function addGoal($requests)
{
    $results = [];
    $requestsNeeded = array("goal", "due", "me");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getUser(array("me" => $requests["me"], "username" => $requests["me"]));
        if (count($row["rows"]) > 0) {
            //checks if email provided is valid using REGEX
            if (preg_match("/\b[\d]{4}-[\d]{2}-[\d]{2}\b/im", $requests["due"])) {
                $db = new pdodb;
                $query = "INSERT INTO Goal (Goal, Due, Upload, Username) VALUES (?,?,?,?);";
                $bindings = array($requests["goal"], $requests["due"], date("y/m/d"), $requests["me"]);
                $goal = $db->query($query, $bindings);

                //if add was ok
                if ($goal > 0) {
                    $results['meta']["ok"] = true;
                    $results['meta']["status"] = 201;
                    $results['meta']["message"] = "Created";

                    $id = $db->lastInsertId();

                    $goal = getGoal($id);

                    $results["rows"] = $goal;
                }
            } else {
                $results["meta"]["feedback"] = "Due date not valid.";
                $results["meta"]["status"] = 400;
                $results["meta"]["ok"] = false;
            }
        } else {
            $results["meta"] = noUserFound($requests['me']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function editGoal($requests)
{
    $results = [];

    $requestsNeeded = array("goalID");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getGoal($requests["goalID"]);
        if (count($row) > 0) {
            $db = new pdodb;
            if (isset($requests['completion'])) {
                $query = "UPDATE Goal SET Completion = NOT Completion WHERE ID = ?;";
                $bindings = array($requests["goalID"]);
            } else if (isset($requests['goal'])) {
                $query = "UPDATE Goal SET Goal = ? WHERE ID = ?;";
                $bindings = array($requests["goal"], $requests["goalID"]);
            }
            $result = $db->query($query, $bindings);

            //if update was ok
            if ($result > 0) {
                $results['meta']["ok"] = true;

                $goal = getGoal($requests["goalID"]);

                $results["rows"] = $goal;
            } else {
                $results['meta']["ok"] = false;
            }
        } else {
            $results["meta"] = noGoalFound($requests['goalID']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function deleteGoal($requests)
{
    $results = [];

    $requestsNeeded = array("goalID");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getGoal($requests["goalID"]);
        if (count($row) > 0) {
            $db = new pdodb;
            $query = "DELETE FROM Comment WHERE GoalID = ?;";
            $bindings = array($requests["goalID"]);
            $db->query($query, $bindings);

            $query = "DELETE FROM Liked WHERE GoalID = ?;";
            $bindings = array($requests["goalID"]);
            $db->query($query, $bindings);

            $query = "DELETE FROM Goal WHERE ID = ?;";
            $bindings = array($requests["goalID"]);
            $row = $db->query($query, $bindings);

            $results['meta']["row"] = $row;

            //if deletion was ok
            if ($row > 0) {
                $results["meta"]["ok"] = true;

                $results["rows"]["GoalID"] = $requests["goalID"];
            } else {
                $results['meta']["ok"] = false;
            }
        } else {
            $results["meta"] = noGoalFound($requests['goalID']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function getComments($requests)
{
    $results = [];

    $requestsNeeded = array("goalID");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getGoal($requests["goalID"]);
        if (count($row) > 0) {
            $db = new pdodb;
            $query = "SELECT * FROM Comment WHERE GoalID = ? ORDER BY Upload, ID;";
            $bindings = array($requests["goalID"]);
            $comments = $db->query($query, $bindings);

            $results["meta"]["ok"] = true;

            $results["rows"] = $comments;
        } else {
            $results["meta"] = noGoalFound($requests['goalID']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function addComment($requests)
{
    $results = [];

    $requestsNeeded = array("comment", "goalID", "me");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getGoal($requests["goalID"]);
        if (count($row) > 0) {
            $row = getUser(array("me" => $requests["me"], "username" => $requests["me"]));
            if (count($row["rows"]) > 0) {
                $db = new pdodb;
                $query = "INSERT INTO Comment (Comment, GoalID, Username, Upload) VALUES (?,?,?,?);";
                $bindings = array($requests["comment"], $requests["goalID"], $requests["me"], date("y/m/d"));
                $comment = $db->query($query, $bindings);

                //if add was ok
                if ($comment > 0) {
                    $results['meta']["ok"] = true;
                    $results['meta']["status"] = 201;
                    $results['meta']["msg"] = "Created";

                    $query = "SELECT * FROM Comment LEFT JOIN (SELECT * FROM Following WHERE Username1 = ?) AS gf ON Comment.Username = username2 WHERE ID = ?;";
                    $bindings = array($requests["me"], $db->lastInsertId());
                    $comment = $db->query($query, $bindings);

                    $results["rows"] = $comment;
                }
            } else {
                $results["meta"] = noUserFound($requests['username']);
            }
        } else {
            $results["meta"] = noGoalFound($requests['goalID']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function editComment($requests)
{
    $results = [];

    $requestsNeeded = array("comment", "commentID");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getComment($requests["commentID"]);
        if (count($row) > 0) {
            $db = new pdodb;
            $query = "UPDATE Comment SET Comment = ? WHERE ID = ?;";
            $bindings = array($requests["comment"], $requests["commentID"]);
            $row = $db->query($query, $bindings);

            //if update was ok
            if ($row > 0) {
                $results['meta']["ok"] = true;

                $comment = getComment($requests["commentID"]);

                $results["rows"] = $comment;
            }
        } else {
            $results["meta"] = noCommentFound($requests['commentID']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function deleteComment($requests)
{
    $results = [];

    $requestsNeeded = array("commentID");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getComment($requests["commentID"]);
        if (count($row) > 0) {
            $db = new pdodb;
            $query = "DELETE FROM Comment WHERE ID = ?;";
            $bindings = array($requests["commentID"]);
            $row = $db->query($query, $bindings);

            //if deletion was ok
            if ($row > 0) {
                $results["meta"]["ok"] = true;

                $results["rows"]["commentID"] = $requests["commentID"];
            }
        } else {
            $results["meta"] = noCommentFound($requests['commentID']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}

function addFriend($requests)
{
    $results = [];

    $requestsNeeded = array("me", "user");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getUser(array("me" => $requests["me"], "username" => $requests["me"]));
        if (count($row["rows"]) > 0) {
            $row = getUser(array("me" => $requests["user"], "username" => $requests["user"]));
            if (count($row["rows"]) > 0) {
                $db = new pdodb;
                $query = "INSERT INTO Following (Username1, Username2) VALUES (?, ?);";
                $bindings = array($requests["me"], $requests["user"]);
                $follow = $db->query($query, $bindings);

                //if add was ok
                if ($follow > 0) {
                    $results['meta']["ok"] = true;
                    $results['meta']["status"] = 201;
                    $results['meta']["message"] = "Created";

                    $query = "SELECT * FROM Following WHERE Username1 = ? AND Username2 = ?;";
                    $bindings = array($requests["me"], $requests["user"]);
                    $follow = $db->query($query, $bindings);

                    $results["rows"] = $follow;
                }
            } else {
                $results["meta"] = noUserFound($requests['me']);
            }
        } else {
            $results["meta"] = noUserFound($requests['user']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }

    return $results;
}

function deleteFriend($requests)
{
    $results = [];

    $requestsNeeded = array("me", "user");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getUser(array("me" => $requests["me"], "username" => $requests["me"]));
        if (count($row["rows"]) > 0) {
            $row = getUser(array("me" => $requests["user"], "username" => $requests["user"]));
            if (count($row["rows"]) > 0) {
                $db = new pdodb;
                $query = "DELETE FROM Following WHERE Username1 = ? AND Username2 = ?;";
                $bindings = array($requests["me"], $requests["user"]);
                $row = $db->query($query, $bindings);

                //if deletion was ok
                if ($row > 0) {
                    $results["meta"]["ok"] = true;

                    $results["rows"]["user"] = $requests["user"];
                } else {
                    $results["meta"] = noUserFound($requests['me']);
                }
            } else {
                $results["meta"] = noUserFound($requests['user']);
            }
        } else {
            $results = requestsNotProvided($requestsNeeded);
        }
    }
    return $results;
}

function addLike($requests)
{
    $results = [];

    $requestsNeeded = array("me", "goalID");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getUser(array("me" => $requests["me"], "username" => $requests["me"]));
        if (count($row["rows"]) > 0) {
            $row = getGoal($requests["goalID"]);
            if (count($row) > 0) {
                $db = new pdodb;
                $query = "INSERT INTO Liked (Username, goalID) VALUES (?, ?);";
                $bindings = array($requests["me"], $requests["goalID"]);
                $like = $db->query($query, $bindings);

                //if add was ok
                if ($like > 0) {
                    $results['meta']["ok"] = true;
                    $results['meta']["status"] = 201;
                    $results['meta']["msg"] = "Created";

                    $query = "SELECT * FROM Liked WHERE Username = ? AND goalID = ?;";
                    $bindings = array($requests["me"], $requests["goalID"]);
                    $like = $db->query($query, $bindings);

                    $results["rows"] = $like;
                }
            } else {
                $results["meta"] = noGoalFound($requests['goalID']);
            }
        } else {
            $results["meta"] = noUserFound($requests['user']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }

    return $results;
}

function deleteLike($requests)
{
    $results = [];

    $requestsNeeded = array("me", "goalID");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getUser(array("me" => $requests["me"], "username" => $requests["me"]));
        if (count($row) > 0) {
            $row = getGoal($requests["goalID"]);
            if (count($row) > 0) {
                $db = new pdodb;
                $query = "DELETE FROM Liked WHERE Username = ? AND GoalID = ?;";
                $bindings = array($requests["me"], $requests["goalID"]);
                $row = $db->query($query, $bindings);

                //if deletion was ok
                if ($row > 0) {
                    $results["meta"]["ok"] = true;

                    $results["rows"]["goalID"] = $requests["goalID"];
                }
            } else {
                $results["meta"] = noGoalFound($requests['goalID']);
            }
        } else {
            $results["meta"] = noUserFound($requests['user']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }

    return $results;
}

function addPicture($requests)
{
    $results = [];

    $results["meta"]["files"] = $_FILES;

    $requestsNeeded = array("username");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getUser(array("me" => $requests["username"], "username" => $requests["username"]));
        if (count($row["rows"]) > 0) {
            $username = $requests['username'];

            $directory = "uploads/";

            $imageFileType = pathinfo(basename($_FILES["picture"]["name"]), PATHINFO_EXTENSION);
            $file = $directory . $username . "." . $imageFileType;

            //check if image file is a actual image or fake image
            $imageType = getimagesize($_FILES["picture"]["tmp_name"]);
            if ($imageType !== false) {
                // if everything is ok, try to upload file
                if (move_uploaded_file($_FILES["picture"]["tmp_name"], $file)) {
                    $db = new pdodb;
                    $query = "UPDATE User SET Picture = ? WHERE Username = ?;";
                    $bindings = array($file, $username);
                    $picture = $db->query($query, $bindings);

                    //if upload was ok
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
        } else {
            $results["meta"] = noUserFound($requests['user']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }

    return $results;
}

function deletePicture($requests)
{
    $results = [];

    $requestsNeeded = array("username");

    if (checkRequestsPresent($requests, $requestsNeeded)) {
        $row = getUser(array("me" => $requests["username"], "username" => $requests["username"]));
        if (count($row["rows"]) > 0) {
            $db = new pdodb;

            $query = "UPDATE User SET Picture = null WHERE Username = ?;";
            $bindings = array($requests["username"]);
            $picture = $db->query($query, $bindings);

            //if update was ok
            if ($picture > 0) {
                $results['meta']["ok"] = true;

                $query = "SELECT Picture FROM User WHERE Username = ?;";
                $bindings = array($requests["username"]);
                $picture = $db->query($query, $bindings);

                $results["rows"] = $picture;
            }
        } else {
            $results["meta"] = noUserFound($requests['user']);
        }
    } else {
        $results = requestsNotProvided($requestsNeeded);
    }
    return $results;
}