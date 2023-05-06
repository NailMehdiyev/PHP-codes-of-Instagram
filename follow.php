<?php
require("constants.php");
require("db.php");
require("function.php");

$method = $_SERVER['REQUEST_METHOD'];
$response = [];
$code = 200;

if (isset($_SERVER['PHP_AUTH_USER'])) {
if ($_SERVER['PHP_AUTH_USER'] == AUTH_USER && $_SERVER['PHP_AUTH_PW'] ==
AUTH_PW) {
if ($method == "POST") {

if (isset($_POST['followerid']) && !empty(trim($_POST['followerid'])) &&
isset($_POST['followingid']) && !empty(trim($_POST['followingid']))) { // follow

$followerid = $_POST['followerid'];
$followingid = $_POST['followingid'];

if (getUserModel($db, $followerid) != 0) { // check follower
if (getUserModel($db, $followingid) != 0) { // check following

$is_following = $db->prepare("SELECT * FROM follows WHERE followerid = :followerid
AND followingid = :followingid");
$is_following->execute(array(

":followerid" => $followerid,
":followingid" => $followingid
));

if ($is_following->rowCount() == 0) { // check if this user is following the other user or not
if ($followerid != $followingid) { // check if the user is trying to follow himself
$follow = $db->prepare("INSERT INTO follows (followerid, followingid) VALUES(:followerid, :followingid)");

$follow->execute(array(
":followerid" => $followerid,
":followingid" => $followingid
));
if ($follow->rowCount() > 0) {
$response['code'] = $code = 200;
$response['message'] = INFO_INSERTED;
} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}
} else { // user is trying to follow himself
$response['code'] = $code = 400;
$response['message'] = ERROR_YOU_CANT_FOLLOW_YOURSELF;
}
} else { // this user is already following the other user
$response['code'] = $code = 400;
$response['message'] = ERROR_ALREADY_FOLLOWING;
}
} else { // there isn't any user with the following's id
$response['code'] = $code = 400;
$response['message'] = ERROR_NOT_FOUND_USER . " (following)";
}
} else { // there isn't any user with the follower's id
$response['code'] = $code = 400;
$response['message'] = ERROR_NOT_FOUND_USER . " (follower)";
}
} else { // bad request
$response['code'] = $code = 400;
$response['message'] = httpStatus($code);
}
} else if ($method == "DELETE") {
if (isset($_GET['followerid']) && !empty(trim($_GET['followerid'])) &&

isset($_GET['followingid']) && !empty(trim($_GET['followingid']))) { // unfollow
$followerid = $_GET['followerid'];
$followingid = $_GET['followingid'];
$unfollow = $db->prepare("DELETE FROM follows WHERE followerid = :followerid AND
followingid = :followingid");
$unfollow->execute(array(

":followerid" => $followerid,
":followingid" => $followingid
));

if ($unfollow->rowCount() > 0) {
$response['code'] = $code = 200;
$response['message'] = INFO_DELETED;

} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}

} else { // bad request
$response['code'] = $code = 400;
$response['message'] = httpStatus($code);
}
} else { // different request method
$response['code'] = $code = 405;
$response['message'] = httpStatus($code);
}
} else { // incorrect username or password
$response['code'] = $code = 401;
$response['message'] = httpStatus($code);
}
} else { // unauthorized
$response['code'] = $code = 401;
$response['message'] = httpStatus($code);
}
setHeader($code);
echo json_encode($response);
?>







