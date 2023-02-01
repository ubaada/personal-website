<?php
// =====================================================
//
//       Uploads images from Summernote editor
//
// ======================================================

// =====================================================
// Authenticate
// =====================================================
// Return db connection AND session details if session valid.
// Ridirects and returns false if session not valid.
function authenticate_session() {
	if(isset($_COOKIE["session_token"])) {
		$session_cookie = htmlspecialchars($_COOKIE["session_token"]);
		// Check if matches in sessions table
		$pdo = new PDO('sqlite:data/data.db');
		$stmt = $pdo->prepare('SELECT * FROM sessions WHERE token = ?');
		$stmt->execute([$session_cookie]);

		$session_match = $stmt->fetch();
		if ($session_match) {
			//return authenticated db connection.
			return [$pdo, $session_match];
		} else {
			//Didn't authorize. Redirect to login.
			$newDest = 'login.php';
			header('Location: '.$newDest);
			return false;
		}
	} else {
		// Cookie not set. Redirect to login.
		$newDest = 'login.php';
		header('Location: '.$newDest);
		return false;
	}
}
[$auth_pdo, $session_details] = authenticate_session();
if ($auth_pdo === false) {return;} // Exit php file if not validated
// =======================================================

// ========================================================
// Upload Script starts here...
// ========================================================
$auth_pdo = null;
if ($_FILES['image']['name']) {
  if (!$_FILES['image']['error']) {
    $name = md5(rand(100, 200));
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = $name.'.'.$ext;
    $destination =  $_SERVER['DOCUMENT_ROOT'].'/cms/data/images/'.$filename;
    $location = $_FILES["image"]["tmp_name"];
    move_uploaded_file($location, $destination);
    echo '/cms/data/images/'.$filename;
  } else {
    echo $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
  }
}

?>