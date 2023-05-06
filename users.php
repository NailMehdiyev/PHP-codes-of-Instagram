<?php
include("constants.php");
include("db.php");
include("function.php");

$method = $_SERVER['REQUEST_METHOD'];
$response = [];
$code = 200;


function getUserList($username = NULL) {

global $db;

$response_temp = [];

if ($username == NULL) {
$query = 'SELECT * FROM users ORDER BY RAND()';
} else {
$query = 'SELECT * FROM users WHERE username LIKE \'%' . $username . '%\'';
}

$users = $db->prepare($query);

$users->execute();

if ($users->rowCount() > 0) {
$users = $users->fetchAll(PDO::FETCH_ASSOC);

for ($i = 0; $i < count($users); $i++) { // get followers and following list & hide passwords and emails

$users[$i] = getUserModel($db, $users[$i]['userid'], 1);

}
$response_temp['code'] = $code = 200;
$response_temp['message'] = httpStatus($code);
$response_temp['users'] = $users;
} else {
$response_temp['code'] = $code = 404;
$response_temp['message'] = ERROR_NOT_FOUND;
$response_temp['users'] = [];

}
return $response_temp;

}


if (isset($_SERVER['PHP_AUTH_USER'])) {

if ($_SERVER['PHP_AUTH_USER'] == AUTH_USER && $_SERVER['PHP_AUTH_PW'] ==AUTH_PW) {

if ($method == "GET") {

if (isset($_GET['username']) && !empty($_GET['username'])) { // filter users by name

$response = getUserList(trim($_GET['username']));

} else if (isset($_GET['userid']) && !empty($_GET['userid'])) {
     // get user details by id
if (getUserModel($db, $_GET['userid']) != 0) {

$response['code'] = $code = 200;
$response['message'] = httpStatus($code);

$response['user'] = getUserModel($db, $_GET['userid'],);

} else {
$response['code'] = $code = 404;
$response['message'] = ERROR_NOT_FOUND_USER;
}
} else { // get all users
$response = getUserList();

}

} else if ($method == "POST") {

if (isset($_POST['username']) && !empty(trim($_POST['username'])) &&
isset($_POST['userpassword']) && !empty(trim($_POST['userpassword'])) 
  ) { 


$username = trim($_POST['username']);
$userpassword = $_POST['userpassword'];


if( isset($_POST['useremail']) && !empty(trim($_POST['useremail']))){// sign up
$useremail = trim($_POST['useremail']);



if (filter_var($useremail, FILTER_VALIDATE_EMAIL)) { // email validation

   if(preg_match('/^[a-zA-Z0-9]{3,25}$/', $username)) { // username validation => english chars+ numbers only & 3-25 characters
   
   $check_email = $db->prepare("SELECT * FROM users WHERE useremail = :useremail");
   $check_email->execute(array(
   ":useremail" =>$useremail));
   
   
   if ($check_email->rowCount() == 0) {
   $check_username = $db->prepare("SELECT * FROM users WHERE username =:username");
   
   $check_username->execute(array(
   ":username" => $username
   ));
   
   if ($check_username->rowCount() == 0) { // create account if username and email were not taken by someone else
   
   $signup = $db->prepare("INSERT INTO users (useremail, username, userpassword)
   VALUES (:useremail, :username,:userpassword)");
   
   $signup->execute(array(":useremail" => $useremail,":username" => $username,":userpassword" => md5($userpassword)));
   
   if ($signup->rowCount() > 0) {
   
   $response['code'] = $code = 200;
   $response['message'] = INFO_REGISTERED;
   
   $response['user'] = getUserModel($db, $db->lastInsertId());



   } else {
   $response['code'] = $code = 400;
   $response['message'] = ERROR_MESSAGE;
   }
   } else {
   $response['code'] = $code = 400;
   $response['message'] = ERROR_USERNAME_TAKEN;
   }
   } else {
   $response['code'] = $code = 400;
   $response['message'] = ERROR_EMAIL_TAKEN;
   }
   } else {
   $response['code'] = $code = 400;
   $response['message'] = ERROR_NOT_VALID_USERNAME;
   }
   } else {
   $response['code'] = $code = 400;
   $response['message'] = ERROR_NOT_VALID_EMAIL;
   }
   
}else{

   
     // sign in

      $signin = $db->prepare("SELECT * FROM users WHERE username = :username AND
      userpassword = :userpassword");
      
      $signin->execute(array(
      ":username" => $username,
      ":userpassword" => md5($userpassword)
      
      ));
      
      if ($signin->rowCount() > 0) {
      
      $response['code'] = $code = 200;
      $response['message'] = httpStatus($code);
      
      $response['user'] = getUserModel($db, $signin->fetchAll(PDO::FETCH_ASSOC)[0]['userid']);
      } else {
      $response['code'] = $code = 404;
      $response['message'] = ERROR_LOGIN;
      }
          
}


} else {

  
if (isset($_FILES['image']) && isset($_POST['userid']) && !empty(trim($_POST['userid']))) { //update profile photo

$userid = $_POST['userid'];

if (getUserModel($db, $userid) == 0) { // check user id

$response['code'] = $code = 404;
$response['message'] = ERROR_NOT_FOUND_USER;

} else {
$image = $_FILES['image'];
$error = $image['error'];
if ($error != 0) {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
} else {

$image_size = $image['size'];

if ($image_size > (1024 * 1024 * 2)) { // size = byte, (1024^2 * 2) = 2 byte
$response['code'] = $code = 400;
$response['message'] = ERROR_IMG_SIZE;

} else {
$image_type = $image['type'];
$image_name = $image['name'];
$image_extension = explode('.', $image_name); // [name, extension]

$image_extension = end($image_extension);   // last index

$new_name = time() . '.' . $image_extension;

$new_path = DIR_PROFILE_PHOTOS . $new_name; // profile_photos/name.extension

if ($image_extension == 'jpg' || $image_extension == 'jpeg' || $image_extension == 'png') {
if (move_uploaded_file($image['tmp_name'], $new_path)){
// get old photo's name before update

$old_photo = $db->prepare("SELECT userphoto FROM users WHERE userid = :userid");
$old_photo->execute(array(
":userid" => $_POST['userid']
));

$old_photo = $old_photo->fetchAll(PDO::FETCH_ASSOC)[0]['userphoto'];

$update_photo = $db->prepare("UPDATE users SET userphoto = :userphoto WHERE
userid = :userid");

$update_photo->execute(array(
":userphoto" => $new_name,
":userid" => $userid
));


if ($update_photo->rowCount() > 0) {

$response['code'] = $code = 200;
$response['message'] = httpStatus($code);

$response['user'] = getUserModel($db, $userid);

if ($old_photo != DEFAULT_PHOTO) { // delete old photo if db update is completed

   unlink(DIR_PROFILE_PHOTOS . $old_photo);

}
} else { // delete photo if db update is not completed
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
unlink($new_path);
}
} else { // file error
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}
} else { // image type error
$response['code'] = $code = 400;
$response['message'] = ERROR_IMG_TYPE;
}
}
}
}
} else { // bad request
$response['code'] = $code = 400;
$response['message'] = httpStatus($code);
}
}
} else if ($method == "PUT") {

$data = json_decode(file_get_contents("php://input"));

if (isset($data->userid) && !empty(trim($data->userid)) &&

isset($data->useremail) && !empty(trim($data->useremail)) &&

isset($data->username) && !empty(trim($data->username)) &&

isset($data->userpassword) && isset($data->userprofileprivate) &&
 ($data->userprofileprivate == 0 || $data->userprofileprivate == 1)) { // update fields
if (filter_var($data->useremail, FILTER_VALIDATE_EMAIL)) { // email validation
if (getUserModel($db, $data->userid) != 0) { // check user id

$parameters = [
":useremail" => trim($data->useremail),

":userfullname" => ($data->userfullname == NULL || empty($data->userfullname)) ? NULL
: trim($data->userfullname),

":userbio" => ($data->userbio == NULL || empty($data->userbio)) ? NULL : trim($data->userbio),

":userprofileprivate" => trim($data->userprofileprivate),

":userid" => trim($data->userid)

];

if (empty($data->userpassword)) {

$query = "UPDATE users SET useremail = :useremail, userfullname = :userfullname,
userbio = :userbio, userprofileprivate = :userprofileprivate WHERE userid = :userid";

} else {

$query = "UPDATE users SET useremail = :useremail, userpassword = :userpassword,
userfullname = :userfullname, userbio = :userbio, userprofileprivate =
:userprofileprivate WHERE userid = :userid";
$parameters[':userpassword'] = md5($data->userpassword);

}

$update_user = $db->prepare($query);
$update_user->execute($parameters);

if ($update_user->rowCount() > 0) {

$response['code'] = $code = 200;
$response['message'] = httpStatus($code);
$response['user'] = getUserModel($db, $data->userid);

} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}
} else {
$response['code'] = $code = 404;
$response['message'] = ERROR_NOT_FOUND_USER;
}
} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_NOT_VALID_EMAIL;
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







