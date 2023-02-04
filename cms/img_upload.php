<?php
// ==== Authenticate Session =============
require('session_auth.php');

// =====================================================
//
//       Uploads images from Summernote editor
//
// ======================================================
if ($_FILES['image']['name']) {
  if (!$_FILES['image']['error']) {
	$tmp_folder = '/cms/images/tmp/';
	if (!file_exists('images/tmp')) {
		// Recurively creates both 'images' & 'tmp'
		mkdir('images/tmp', 0777, true);
	}
    $name = bin2hex(random_bytes(4));
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = $name.'.'.$ext;
    $destination =  $_SERVER['DOCUMENT_ROOT'].$tmp_folder . $filename;
    $location = $_FILES["image"]["tmp_name"];
    move_uploaded_file($location, $destination);
    echo $tmp_folder.$filename;
  } else {
    echo $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
  }
}

?>