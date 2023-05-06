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
if (isset($_POST['commenttext']) && !empty(trim($_POST['commenttext'])) &&
isset($_POST['commentpost']) && !empty(trim($_POST['commentpost'])) &&
isset($_POST['commentowner']) && !empty(trim($_POST['commentowner']))) { // share comment


$commenttext = trim($_POST['commenttext']);
$commentpost = $_POST['commentpost'];
$commentowner = $_POST['commentowner'];
if (getUserModel($db, $commentowner) != 0) { // check user id
if (getPostModel($db, $commentpost) != 0) { // check post id
 $createdat = date("M d, Y - h:i A"); // Oct 16, 2022 - 16:23 PM

$share_comment = $db->prepare("INSERT INTO comments (commenttext, commentpost,
commentowner, createdat) VALUES(:commenttext, :commentpost, :commentowner,
:createdat)");

$share_comment->execute(array(
":commenttext" => $commenttext,
":commentpost" => $commentpost,
":commentowner" => $commentowner,
":createdat" => $createdat
));


if ($share_comment->rowCount() > 0) {
$response['code'] = $code = 200;
$response['message'] = INFO_INSERTED;
} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}

} else { // there isn't any post with this id
$response['code'] = $code = 404;
$response['message'] = ERROR_NOT_FOUND_POST;
}

} else { // there is not a user with this id
$response['code'] = $code = 404;
$response['message'] = ERROR_NOT_FOUND_USER;
}

} else { // bad request
$response['code'] = $code = 400;
$response['message'] = httpStatus($code);
}

} else if ($method == "PUT") {
$data = json_decode(file_get_contents("php://input"));

if (isset($data->commentid) && !empty(trim($data->commentid)) &&
isset($data->commenttext) && !empty(trim($data->commenttext))) { // update comment
$update_comment = $db->prepare("UPDATE comments SET commenttext = :commenttext
WHERE commentid = :commentid");

$update_comment->execute(array(
":commenttext" => trim($data->commenttext),
":commentid" => $data->commentid
));


if ($update_comment->rowCount() > 0) {
$response['code'] = $code = 200;
$response['message'] = INFO_UPDATED;

} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}
} else { // bad request
$response['code'] = $code = 400;
$response['message'] = httpStatus($code);
}
} else if ($method == "DELETE") {

if (isset($_GET['commentid']) && !empty(trim($_GET['commentid']))) { // delete comment
$commentid = $_GET['commentid'];

$delete_comment = $db->prepare("DELETE FROM comments WHERE commentid =
:commentid");

$delete_comment->execute(array(
":commentid" => $commentid
));

if ($delete_comment->rowCount() > 0) {
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





