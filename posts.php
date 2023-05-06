<?php
require("constants.php");
require("db.php");
require("function.php");

$method = $_SERVER['REQUEST_METHOD'];
$response = [];
$code = 200;



function getPostList($userid, $is_feed) {

global $db;
$response_temp = [];
if ($is_feed == 1) { // feed eger izliyen takib atan bizikse bizim useriddirse gelsin o tablodan izlediyimizi getirsin

$following = $db->prepare("SELECT followingid FROM follows WHERE followerid =
:userid");

$following->execute(array(//qarsi terefin yeni izlediyimiz useri getiririk evvel
":userid" => $userid
));

$following = $following->fetchAll(PDO::FETCH_COLUMN);
array_push($following, $userid);

$following = implode(",", $following);

$query = "SELECT * FROM posts WHERE postowner IN(" . $following . ") ORDER BY
postid DESC";

} else { // posts (in user profile)
$query = "SELECT * FROM posts WHERE postowner = " . $userid . " ORDER BY postid
DESC";
}
$posts =$db->prepare($query);

$posts->execute();

if ($posts->rowCount() > 0) {

$posts = $posts->fetchAll(PDO::FETCH_ASSOC);//listeliyir array halinda

for ($i = 0; $i < count($posts); $i++) {

$posts[$i] = getPostModel($db, $posts[$i]['postid']);//paylasilan posta çevirir

}
$response_temp['code'] = $code = 200;

$response_temp['message'] = httpStatus($code);

$response_temp['posts'] = $posts;

} else { // there isn't any post

$response_temp['code'] = $code = 404;

$response_temp['message'] = ERROR_NOT_FOUND;

$response_temp['posts'] = [];
}

return $response_temp;

}



if (isset($_SERVER['PHP_AUTH_USER'])) {

if ($_SERVER['PHP_AUTH_USER'] == AUTH_USER && $_SERVER['PHP_AUTH_PW'] ==AUTH_PW) {

if ($method == "GET") {

if (isset($_GET['userid']) && !empty(trim($_GET['userid'])) && isset($_GET['isfeed']) &&


($_GET['isfeed'] == 0 || $_GET['isfeed'] == 1)) {

$user_id = $_GET['userid'];
$is_feed = $_GET['isfeed'];

$response = getPostList($user_id, $is_feed);
} else if (isset($_GET['postid']) && !empty(trim($_GET['postid']))) { // post details
$post_id = $_GET['postid'];

if (getPostModel($db, $post_id) != 0) {

$response['code'] = $code = 200;
$response['message'] = httpStatus($code);

$response['post'] = getPostModel($db, $post_id);

} else { // there isn't any post with this id

$response['code'] = $code = 404;
$response['message'] = ERROR_NOT_FOUND_POST;
}
} else { // bad request
$response['code'] = $code = 400;
$response['message'] = httpStatus($code);
}
} else if ($method == "POST") {
if (isset($_FILES["image"]) && isset($_POST['postdescription']) &&!empty(trim($_POST['postdescription'])) &&
isset($_POST['postowner']) && !empty(trim($_POST['postowner']))) {
$post_description = trim($_POST['postdescription']);
$post_owner = trim($_POST['postowner']);
if (getUserModel($db, $post_owner) != 0) { // check user id & share post
$image =$_FILES["image"];
$error = $image['error'];
if($error != 0) {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
} else {
$image_size = $image['size'];
if ($image_size > (1024 * 1024 * 2)) { // size = byte, (1024^2 * 2) = 2 byte
$response['code'] = $code = 400;
$response['message'] = ERROR_IMG_SIZE;
} else {
  $image_type =$image['type'];

  $imagename=$image['name'];

  $image_temp=$image['tmp_name'];
 // $target='C:/xampp/htdocs/jer/images/'.basename($image['name']);

  //$ext=pathinfo($image['name'],PATHINFO_EXTENSION);
  $image_extens = explode('.', $imagename);   // [name, extension]
  $image_extension = end($image_extens);      //$image_extension[count($image_extension) - 1]; // last index

  $filename='uploading/'.time().".".$image_extension;


  $res=move_uploaded_file($image_temp,$filename);
   

if ($image_extension == 'jpg' || $image_extension == 'jpeg' || $image_extension == 'png') {
if ($res){

// $uploads_dir = 'uploads/';
// $name = $_FILES['userfile']['name'];
// if (is_uploaded_file($_FILES['userfile']['tmp_name']))
// {       
//     //in case you want to move  the file in uploads directory
//      move_uploaded_file($_FILES['userfile']['tmp_name'], $uploads_dir.$name);
//      echo 'moved file to destination directory';
//      exit;
// }


$createdat = date("M d, Y - h:i A"); // Oct 16, 2022 - 16:23 PM

$share_post = $db->prepare("INSERT INTO posts (postphoto, postdescription,postowner,
createdat) VALUES(:postphoto, :postdescription, :postowner, :createdat)");
$share_post->execute(array(
":postphoto" => $filename,
":postdescription" => $post_description,
":postowner" => $post_owner,
":createdat" => $createdat
));
if ($share_post->rowCount() > 0) {
$response['code'] = $code = 200;
$response['message'] = INFO_INSERTED;
} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
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

if(!empty($data->postid)){

  print_r($data->postid);

}

if (isset($data->postid) && !empty(trim($data->postid)) &&

isset($data->postdescription) && !empty(trim($data->postdescription))) { // update post
if (getPostModel($db, $data->postid) != 0) { // check post id
$update_post = $db->prepare("UPDATE posts SET postdescription = :postdescription
WHERE postid = :postid");

$update_post->execute(array(
":postdescription" => trim($data->postdescription),
":postid" => $data->postid
));


if ($update_post->rowCount() > 0) {
$response['code'] = $code = 200;
$response['message'] = INFO_UPDATED;
} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}
} else {
$response['code'] = $code = 404;
$response['message'] = ERROR_NOT_FOUND_POST;
}
} else { // bad request
$response['code'] = $code = 400;
$response['message'] = httpStatus($code);
}
} else if ($method == "DELETE") {
if (isset($_GET['postid']) && !empty(trim($_GET['postid']))) { // delete post
$post_id = $_GET['postid'];
$post_photo = $db->prepare("SELECT postphoto FROM posts WHERE postid =:postid");
$post_photo->execute(array(":postid" =>$post_id ));

if ($post_photo->rowCount() > 0) {
$post_photo = $post_photo->fetchAll(PDO::FETCH_ASSOC)[0]['postphoto'];


if (unlink($post_photo)) { // delete post photo  time()."-".$imagename.$image_extension

$delete_post = $db->prepare("DELETE FROM posts WHERE postid = :postid");
$delete_post->execute(array(
":postid" => $post_id
));

if ($delete_post->rowCount() > 0) {
$response['code'] = $code = 200;
$response['message'] = INFO_DELETED;
} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}
} else {
$response['code'] = $code = 400;
$response['message'] = ERROR_MESSAGE;
}
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






