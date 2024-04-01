<?php
// ==== Authenticate Session =============
require('session_auth.php');

/* =====================================================
 *
 *           Backend for CMS File System Ops
 * 
 * ======================================================
 * 
 * /?op=filesinfo?key=KEY             [GET]  - Get files info json from KEY folder
 * /?op=upload?key=KEY                [POST]  - Upload file to KEY folder
 * /?op=delete?key=KEY?filename=fn    [DELETE]  - Delete file from KEY folder
 * /?op=rename?key=KEY?filename=fn?newname=nn    [PUT]  - Rename file in KEY folder
 * 
 */

$files_folder = '/cms/post_files/';

if ($_GET['op'] == 'filesinfo') {
  get_files_info($_GET['key'], $files_folder);
} else if ($_GET['op'] == 'upload') {
  upload_file($_GET['key'], $files_folder);
} else if ($_GET['op'] == 'delete') {
  delete_file($_GET['key'], $files_folder, $_GET['filename']);
} else if ($_GET['op'] == 'rename') {
  rename_file($_GET['key'], $files_folder, $_GET['filename'], $_GET['newname']);
}

/* Rename file in [KEY] folder */
function rename_file($key, $files_folder, $filename, $newname) {
  $target_dir = $_SERVER['DOCUMENT_ROOT'].$files_folder.$key.'/';
  $target_file = $target_dir . $filename;
  $new_file = $target_dir . $newname;
  if (file_exists($target_file)) {
    rename($target_file, $new_file);
    echo "The file $filename has been renamed to $newname.";
  } else {
    echo "Sorry, the file $filename does not exist.";
  }
}

/* Upload in [KEY] folder */
function upload_file($key, $files_folder) {
  $target_dir = $_SERVER['DOCUMENT_ROOT'].$files_folder.$key.'/';
  if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
  }
  $target_file = $target_dir . basename($_FILES["file"]["name"]);
  if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
    echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
  } else {
    echo "Sorry, there was an error uploading your file.";
  }
}

/* Delete file from [KEY] folder */
function delete_file($key, $files_folder, $filename) {
  $target_dir = $_SERVER['DOCUMENT_ROOT'].$files_folder.$key.'/';
  $target_file = $target_dir . $filename;
  if (file_exists($target_file)) {
    unlink($target_file);
    echo "The file $filename has been deleted.";
  } else {
    echo "Sorry, the file $filename does not exist.";
  }
}

/* Get files info from [KEY] folder */
function get_files_info($key, $files_folder) {
  $target_dir = $_SERVER['DOCUMENT_ROOT'].$files_folder.$key.'/';
  $files = scandir($target_dir);
  $files = array_diff($files, array('.', '..'));
  $files_info = array();
  foreach ($files as $file) {
    $file_info = array();
    $file_info['name'] = $file;
    $file_info['size'] = filesize($target_dir.$file);
    $file_info['date'] = date("F d Y H:i:s.", filemtime($target_dir.$file));
    array_push($files_info, $file_info);
  }
  echo json_encode($files_info);
}
?>