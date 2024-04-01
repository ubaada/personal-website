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
 * /?op=getmaxsize                    [GET]  - Get max file size allowed
 * 
 */

$files_folder = '/cms/post_files/';

// Check if operation is set
if (!isset($_GET['op'])) {
  echo "Invalid Operation";
  return;
}

if ($_GET['op'] == 'filesinfo') {
  get_files_info($_GET['key'], $files_folder);
} else if ($_GET['op'] == 'upload') {
  upload_file($_GET['key'], $files_folder);
} else if ($_GET['op'] == 'delete') {
  delete_file($_GET['key'], $files_folder, $_GET['filename']);
} else if ($_GET['op'] == 'rename') {
  rename_file($_GET['key'], $files_folder, $_GET['filename'], $_GET['newname']);
} else if ($_GET['op'] == 'getmaxsize') {
  get_max_file_size();
} else {
  echo "Invalid Operation";
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
  // Check if file size bigger than allowed
  $max_size = max_file_size();
  if ($_FILES["file"]["size"] > $max_size) {
    echo "Sorry, your file is too large. Max file size is $max_size.
    Your file size is ".$_FILES["file"]["size"];
    http_response_code(413);
    return;
  }
  $target_dir = $_SERVER['DOCUMENT_ROOT'].$files_folder.$key.'/';
  if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
  }
  $target_file = $target_dir . basename($_FILES["file"]["name"]);
  if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
    echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
  } else {
    http_response_code(500);
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

/* Get max file size allowed in bytes */
function max_file_size() {
  $max_size = ini_get('upload_max_filesize');
  // Convert to bytes
  $max_size = trim($max_size);
  $last = strtolower($max_size[strlen($max_size)-1]);
  $max_size = floatval($max_size);
  switch($last) {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
          $max_size *= (1024 * 1024 * 1024); //1073741824
          break;
      case 'm':
          $max_size *= (1024 * 1024); //1048576
          break;
      case 'k':
          $max_size *= 1024;
          break;
  }
  return $max_size;
}

function get_max_file_size() {
  echo max_file_size();
}
?>