<?php
/*
 * A RESTful API router.
 * @author 733474
*/

//include all files needed
include __DIR__.'/../../inc/all.php';

//get the method
$method = $_SERVER['REQUEST_METHOD'];

//get the path to decide what happens
$path = explode('/', ltrim($_SERVER['PATH_INFO'], "/"));

//gets the data into array
$requests = $_REQUEST;

//do relevant stuff with path[1]
switch ($path[0]) {
    case "users":
        switch ($method) {
            case "GET":
                $requests["username"] = $path[1];
                $results = getUser($requests);
                break;
            case "PUT":
                $requests["username"] = $path[1];
                $results = addUser($requests);
                break;
            case "PATCH":
                $requests["username"] = $path[1];
                $results = editUser($requests);
                break;
            default:
                $results["meta"] = methodNotAllowed();
        }
        break;
    case "goals":
        switch ($method) {
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
            default:
                $results["meta"] = methodNotAllowed();
        }
        break;
    case "comments":
        switch ($method) {
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
            default:
                $results["meta"] = methodNotAllowed();
        }
        break;
    case "follows":
        switch ($method) {
            case "POST":
                $results = addFriend($requests);
                break;
            case "DELETE":
                $results = deleteFriend($requests);
                break;
            default:
                $results["meta"] = methodNotAllowed();
        }
        break;
    case "likes":
        switch ($method) {
            case "POST":
                $results = addLike($requests);
                break;
            case "DELETE":
                $results = deleteLike($requests);
                break;
            default:
                $results["meta"] = methodNotAllowed();
        }
        break;
    case "pictures":
        switch ($method) {
            case "POST":
                $results = addPicture($requests);
                break;
            case "DELETE";
                $results = deletePicture($requests);
                break;
            default:
                $results["meta"] = methodNotAllowed();
        }
        break;
    default:
        $results["meta"]["ok"] = false;
        $results["meta"]["status"] = 404;
        $results["meta"]["feedback"] = "Unrecognised URI.";
        $results["meta"]["message"] = "Not Found";
}

//send back the data provided
$results['meta']["requests"] = $requests;
//send back the method requested
$results['meta']["method"] = $method;
//send back the path they requested
$results['meta']["path"] = $path;

//check if requested to send json
$json = !(stripos($_SERVER['HTTP_ACCEPT'], 'application/json') === false);

//check is everything was ok
if (isset($results["meta"]["ok"]) && $results["meta"]["ok"] !== false) {
    $status = isset($results["meta"]["status"]) ? $results["meta"]["status"] : 200;
    $message = isset($results["meta"]["message"]) ? $results["meta"]["message"] : "OK";
} else {
    $status = isset($results["meta"]["status"]) ? $results["meta"]["status"] : 500;
    $message = isset($results["meta"]["message"]) ? $results["meta"]["message"] : "Internal Server Error";
}

$results["meta"]["status"] = $status;
$results["meta"]["message"] = $message;

header("HTTP/1.1 $status $message");

//send the results, send by json if json was requested
if ($json) {
    //
    header("Content-Type: application/json");
    echo json_encode($results);
} else { //else send by plain text
    header("Content-Type: text/plain");
    echo("results: ");
    var_dump($results);
}