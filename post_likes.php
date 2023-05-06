<?php
	require("constants.php");
	require("db.php");
	require("function.php");
	
	$method = $_SERVER['REQUEST_METHOD'];
	$response = [];
	$code = 200;

	if (isset($_SERVER['PHP_AUTH_USER'])) {
		if ($_SERVER['PHP_AUTH_USER'] == AUTH_USER && $_SERVER['PHP_AUTH_PW'] == AUTH_PW) {
			if ($method == "POST") {
        if (isset($_POST['userid']) && !empty(trim($_POST['userid'])) &&
        isset($_POST['postid']) && !empty(trim($_POST['postid']))) { // like
					$user_id = $_POST['userid'];
					$post_id = $_POST['postid'];

          if (getUserModel($db, $user_id) != 0) { // check user id
						if (getPostModel($db, $post_id) != 0) { // check post id
							$is_liked = $db->prepare("SELECT * FROM likedposts WHERE postid = :postid AND userid = :userid");
							$is_liked->execute(array(
								":postid" => $post_id,
								":userid" => $user_id
							));

							if ($is_liked->rowCount() == 0) { // check if this post isn't liked before by this user
								$like = $db->prepare("INSERT INTO likedposts (postid, userid) VALUES(:postid, :userid)");
								$like->execute(array(
									":postid" => $post_id,
									":userid" => $user_id
								));

								if ($like->rowCount() > 0) {
									$response['code'] = $code = 200;
									$response['message'] = INFO_INSERTED;
								} else {
									$response['code'] = $code = 400;
									$response['message'] = ERROR_MESSAGE;
								}
							} else { // this post is already liked by this user
								$response['code'] = $code = 400;
								$response['message'] = ERROR_ALREADY_LIKED;
							}
						} else { // there isn't any post with this id
							$response['code'] = $code = 404;
							$response['message'] = ERROR_NOT_FOUND_POST;
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
        if (isset($_GET['userid']) && !empty(trim($_GET['userid'])) &&
        isset($_GET['postid']) && !empty(trim($_GET['postid']))) { // unlike
					$user_id = $_GET['userid'];
					$post_id = $_GET['postid'];

					$unlike = $db->prepare("DELETE FROM likedposts WHERE postid = :postid AND userid = :userid");
					$unlike->execute(array(
						":postid" => $post_id,
						":userid" => $user_id
					));

					if ($unlike->rowCount() > 0) {
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
