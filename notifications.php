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
if ($method == "GET") {
if (isset($_GET['userid']) && !empty(trim($_GET['userid']))) { // get notifications
$user_id = $_GET['userid'];
if (getUserModel($db, $userid) != 0) { // check user id
// get all notifications

$notifications = $db->prepare("SELECT * FROM notifications WHERE notificationreceiver =
:notificationreceiver ORDER BY notificationid DESC");
$notifications->execute(array(
":notificationreceiver" => $user_id
));

if ($notifications->rowCount() > 0) {
$response['code'] = $code = 200;
$response['message'] = httpStatus($code);
// get unseen notifications count

$unseen_notifications = $db->prepare("SELECT * FROM notifications WHERE
notificationreceiver = :notificationreceiver AND isseen = 0");
$unseen_notifications->execute(array(
":notificationreceiver" => $user_id
));

$response['unseennotificationcount'] = $unseen_notifications->rowCount();
$notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);
for ($i = 0; $i < count($notifications); $i++) { // get notification resources & remove notification type and receiver // user
if ($notifications[$i]['notificationtype'] == 0) { 
$notifications[$i]['user'] = getUserModel($db, $notifications[$i]['notificationresource'], 1, 0);
$notifications[$i]['post'] = new stdClass;
} else { // post
$notifications[$i]['user'] = new stdClass;
$notifications[$i]['post'] = getPostModel($db, $notifications[$i]['notificationresource'], 0);
}
unset($notifications[$i]['notificationresource']);
unset($notifications[$i]['notificationtype']);
unset($notifications[$i]['notificationreceiver']);
}
$response['notifications'] = $notifications;
} else { // there is nothing in here
$response['code'] = $code = 404;
$response['message'] = ERROR_NOT_FOUND;
}
} else { // there isn't any user with this id
$response['code'] = $code = 404;
$response['message'] = ERROR_NOT_FOUND_USER;
}
} else { // bad request
$response['code'] = $code = 400;
$response['message'] = httpStatus($code);
}
} else if ($method == "PUT") {
    
$data = json_decode(file_get_contents("php://input"));

if (isset($data->userid) && !empty(trim($data->userid))) { // update notifications (mark all asseen)

if (getUserModel($db, $data->userid) != 0) { // check user id
$update_notifications = $db->prepare("UPDATE notifications SET isseen = 1 WHERE
notificationreceiver = :notificationreceiver");

$update_notifications->execute(array(
":notificationreceiver" => $data->userid
));

if ($update_notifications->rowCount() > 0) {
$response['code'] = $code = 200;
$response['message'] = INFO_UPDATED;
} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}
} else { // there isn't any user with this id
$response['code'] = $code = 404;
$response['message'] = ERROR_NOT_FOUND_USER;
}
} else { // bad request
$response['code'] = $code = 400;
$response['message'] = httpStatus($code);
}
} else if ($method == "DELETE") {
if (isset($_GET['userid']) && !empty(trim($_GET['userid']))) { // delete all notifications
$user_id = $_GET['userid'];
if (getUserModel($db, $user_id) != 0) { // check user id
$delete_notifications = $db->prepare("DELETE FROM notifications WHERE
notificationreceiver = :notificationreceiver");
$delete_notifications->execute(array(
":notificationreceiver" => $_GET['userid']
));
if ($delete_notifications->rowCount() > 0) {
$response['code'] = $code = 200;
$response['message'] = INFO_DELETED;
} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}
} else { // there isn't any user with this id
$response['code'] = $code = 400;
$response['message'] = httpStatus($code);
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









