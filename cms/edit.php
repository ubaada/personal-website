<?php
// ==== Authenticate Session =============
require('session_auth.php');

/* ========================================================
 *
 *           Create and Edits Posts
 * 
 * ========================================================
 * 
 *	Access Pattern:
 *	1. /		:	[GET] 	Redirect to CMS home.
 *	   				[POST] 	Posted (empty) from CMS home not from itself.
 *							Create new post in db. Redirect to /edit?key=KEY.
 *	2. if /key	:	[GET]	Load stored data into page WHERE key.
 *							Show the Save button.
 *					[POST]	Update post from POST data.
 *							Reidrect to GET.
 *                 [DELETE]	Delete current post.
 *                          Redirect to /edit home.
 */

// Placeholder post variable
date_default_timezone_set('Pacific/Auckland');
$post_details = array("post_id"=>"",
						"username"=>"",
						"title"=>"",
						"date"=>time(),
						"content"=>"",
						"status"=>"draft",
						"tags"=>"",
						"views"=>""
						);
$files_folder = '/cms/post_files/';

$error = "";
if (isset($_GET['key'])) { // Indvidual post address /?key=
	$post_key = $_GET['key'];
	if ($_SERVER['REQUEST_METHOD']==='POST') {
		// Update existing post
		$dateimestamp = strtotime($_POST["date"]);
		
		//replace old tmp img urls with KEY folder
		[$new_content, $used_tmpfiles] = replace_img_URLs($_POST["content"], $_GET['key']);
		
		$stmt = $auth_pdo->prepare('UPDATE posts SET title = ?, date=?, content=?, status=?,tags=? WHERE post_id = ?');
		$stmt->execute([$_POST["title"], 
						$dateimestamp, 
						$new_content, 
						$_POST["status"],
						$_POST["tags"],
						$_GET['key']
					]);
		
		if ($stmt){
			// Save successfull
			notify("Saved");
		} else {
			// Post did not update.
			http_response_code(404);
			echo "Post update failed.";
			//include('my_404.php');
		}
		die();
	} else if ($_SERVER['REQUEST_METHOD']==='DELETE') {
		// Delete the post
		$stmt = $auth_pdo->prepare('DELETE FROM posts WHERE post_id=? AND username=?');
		$stmt->execute([$_GET['key'],
						$session_details['username']
					]);
		
		// Delete the folder if empty
		$directory = $files_folder.$_GET['key'].'/';
		if (is_dir($directory)) {
			$files = scandir($directory);
			if (count($files) == 2) { // 2 because of '.' and '..'
				rmdir($directory);
			}
		}
		// Post deleted. Redirect to the edit page
		$newDest = 'index';
		header('Location: '.$newDest);
		exit;
	} else if ($_SERVER['REQUEST_METHOD']==='GET') {
		$stmt = $auth_pdo->prepare('SELECT * FROM posts WHERE post_id = ? AND username = ?');
		$stmt->execute([$post_key, $session_details['username']]);
		$post_details = $stmt->fetch();
	
		if (!$post_details){
				// Post not found. Return 404 and exit.
				http_response_code(404);
				echo "404";
				//include('my_404.php');
				die();
		}
	}
} else { // Root / address
	if ($_SERVER['REQUEST_METHOD']==='POST') {
		// Insert new post into db.
		// NOTHING expected to provided by the user.

		// Generate a unique random post_id
		$max_iterations = 10; 
		// log something to apache log
		error_log("Creating new post...");
		do {
			// Using base16 because of consistent result length
			// only base(2^n) divide bytes(8bit) evenly.
			// Base36 (26 alph + 10 nums) isn't a power of 2.
			$rand_post_id = bin2hex(random_bytes(4));
			
			// Check if the post_id exists in the db already
			$stmt = $auth_pdo->prepare('SELECT post_id FROM posts WHERE post_id = ?');
			$stmt->execute([$rand_post_id]);
			
			if (!$stmt->fetch()) {
				// If post_id does not exist, break the loop.
				break;
			}
			// log an error to apache log
			error_log("Post ID collision. Retrying...");
			
			$max_iterations--;
		} while ($max_iterations > 0);
		error_log("Passes: $max_iterations");
		if ($max_iterations == 0) {
			// Post ID generation failed.
			http_response_code(404);
			echo "Post creation failed.";
			//include('my_404.php');
			die();
		}

		// Current date and time of the server
		$dateimestamp = time();
		$stmt = $auth_pdo->prepare('INSERT INTO posts (username, post_id, date, status) VALUES (?,?,?,?)');
		$stmt->execute([$session_details['username'],
						$rand_post_id, 
						$dateimestamp, 
						"draft"
					]);
		if ($stmt){
			// Post created. Redirect to edit page.
			echo "Post created.";
			$newDest = '?key='.$rand_post_id;
			header('Location: '.$newDest);
			exit;
		} else {
			// Post not created.
			http_response_code(404);
			echo "Post creation failed.";
			$newDest = '/cms';
			//header('Location: '.$newDest);
			exit;
		}
	} else if ($_SERVER['REQUEST_METHOD']==='GET') {
		// Redirect to CMS home
		$newDest = '/cms';
		echo "GET sent. Redirecting...";
		//header('Location: '.$newDest);
		exit;
	}
	
}

$auth_pdo = null;

// Checks which permanently stored images were reused in
// the editing phase and which were discarded. Calculate the
// difference to delete those files from the server.
// (Performed before moving tmp (new) images over.)
function delete_unused_images($content, $key) {
	// Regex matches src='/cms/images/[key]/[filename]'
	$reg = "^src=[\'\"]\/cms\/images\/$key\/([a-zA-Z0-9]*\.[a-zA-Z0-9]*)[\'\"]^";
	$directory = "images/$key/";
	$matches = []; // Matches will be transfered here
	preg_match_all($reg, $content, $matches);
	$reused_files = $matches[1];
	$files = scandir($directory);
	foreach ($files as $file) {
	  if (!in_array($file, $reused_files) && !is_dir($directory . $file)) {
		unlink($directory . $file);
	  }
	}
}
// Replace temperory img locations with new ones after saving
function replace_img_URLs($content, $key) {
	// Regex matches src='/cms/images/tmp/[filename]'
	$reg = '^src=[\'\"]\/cms\/images\/tmp\/([a-zA-Z0-9]*\.[a-zA-Z0-9]*)[\'\"]^';
	$new_url = "src='/cms/images/$key/$1'";
	$matches = []; // Matches will be transfered here
	preg_match_all($reg, $content, $matches);
	$filenames = $matches[1];
				
	$new_content = preg_replace($reg, $new_url, $content);
	
	return [$new_content, $filenames];
}
// Actually move the images from tmp folder to post folder
function move_tmp_images($filenames, $key) {
	// Create key folder if it doesn't exist
	// it will exist for old posts already
	echo "LETETETS go";
	if (!file_exists("images/$key")) {
		mkdir("images/$key", 0777, true);
	}
	
	foreach ($filenames as $file) {
		rename("images/tmp/$file", "images/$key/$file");
	}
	// empty the tmp folder
	$files = glob('images/tmp/*');  //get all file names
	foreach($files as $file){  // iterate files
	  if(is_file($file)) {
		unlink($file);  //delete file
	  }
	}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Tags which *must* come first in the header -->
  <meta charset="utf-8">
  <!--Let browser know website is optimized for mobile-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1"/>
  <title>
    Post Editor
  </title>

  <link rel="icon" type="image/x-icon" href="/images/favicon.png">

  <!--Let browser know website is optimized for mobile-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Font(s) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300&display=swap" rel="stylesheet">


  <!-- Related to Summernote text editor --->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

	<!-- include codemirror (codemirror.css, codemirror.js, xml.js, formatting.js)-->
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/codemirror.min.css" />
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/theme/blackboard.min.css">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/theme/monokai.min.css">
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/codemirror.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/mode/xml/xml.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/addon/search/searchcursor.min.js"></script>


  
    <!-- Main css file-->
  <link href="/css/common.css" rel="stylesheet">

  
  <style>

	/* flexbox */
    #post_edit {
	  display:flex;
	  flex-direction:column;
	  min-height:100vh;
    }

    textarea {
      clear: both;
    }

	input,
    textarea {
      border: 1px solid var(--textcolor);
      padding: 10px;
      border-radius: 4px;
      margin: 10px 0px;
	  color: var(--textcolor);
	  box-sizing: border-box;
    }
	input[type=button] {
		border:none;
		color: var(--bgcolor);
		background-color: var(--textcolor);
		cursor: pointer;
	}
	/* disable button */
	input[type=button]:disabled {
		background-color: var(--disabled-color);
		cursor: not-allowed;
	}
	
	
	
	#publish_btn {
		display: inline;
		padding: 8px 10px 10px 2px;
		margin-left: 6px;
		border-left: 8px solid;
	}
	#publish_btn::before {
		content:'Draft';
	}
	input:checked+#publish_btn {
		border-left:0px;
		padding-left:8px;
		color: var(--bgcolor);
		background-color: var(--textcolor);
	}
	input:checked+#publish_btn::before {
		content:'Publish';
	}
	
	.bottom-row {
		position: sticky;
		bottom: 0px;
		background-color: var(--bgcolor);
		display: flex;
		justify-content: space-between;
		align-items: center;
		column-gap: 0.875rem;
		border-top: 1px solid var(--footer-bg-color);
		z-index: 10;
		font-size: 10px;
		flex-wrap: wrap;
	}
	#status_bar {
		display: flex;
		align-items: center;
		gap: 0.5rem;
		padding: 0.5rem;
	}
	#file_bar {
		display: flex;
		align-items: center;
		gap: 0.5rem;
	}
	
	/* overiding summernote's css */
	.note-frame {
		color: var(--textcolor);
		flex-grow: 1;
		display:flex;
		flex-direction:column;
	}
	.note-btn {
		background-color:var(--bgcolor);
		color: var(--textcolor);
		border:none !important;
	}
	.CodeMirror {
		font-size: 16px;
	}
	.note-toolbar {
		position: sticky;
		top: 0px;
		z-index: 10;
		border: 1px solid var(--textcolor) !important
	}
	
	.note-toolbar,a.note-dropdown-item, a.note-dropdown-item:hover, .note-modal-content,.note-modal-title,.note-form-label,.note-dropdown-menu {
		color: var(--textcolor) !important;
		background: var(--bgcolor) !important;
		border: 1px solid var(--bgcolor);
	}
	
	.note-editor .note-editing-area .note-editable a, .note-editor .note-editing-area .note-editable a:hover{
		text-decoration: unset;
		font-family: unset;
		font-weight: unset;
		color: unset;
	}
	.note-editor {
		border: unset !important;
	}
	.note-editing-area {
		flex-grow:1;
	}
	.note-editable {
		height: 100%;
	}
	.note-editable *:active {
		background-color: var(--footer-bg-color);
	}
	/* Overide article size style in editor*/
	.article {
		width:unset;
	}

	/*For file explorer. Pops up over everything*/
	#files_button {
	}
	#file_explorer_container {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: rgba(0,0,0,0.5);
		z-index: 100;
		visibility: hidden;
		padding:20px;
	}
	#file_explorer_box {
		position: fixed;
		background-color: var(--bgcolor);
		border: 1px solid var(--textcolor);
		padding: 10px;
		display: flex;
    	flex-direction: column;
		/* Center the box whatever the width */
		left: 50%;
		top: 50%;
		transform: translate(-50%, -50%);
		width: 80%;
		height: 80%;
	}

	#file_explorer_box .upper_bar {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	#file_explorer_box .close_btn {
		padding: 10px;
		margin: 10px;
		cursor: pointer;
		color: var(--textcolor);
	}
	#file_explorer_box .title {
		margin: 0;
		padding: 5px;
	}
	#file_explorer_box .progressbar_container {
		width: 100%;
		height: 2px;
		margin-bottom: 10px;
	}
	#file_explorer_box #file-progressbar {
		height: 100%;
		width: 0%;
		visibility: hidden;
		background-color: var(--accent-color);
		transition: width 1s;
	}
	#file_explorer_box #file_list {
		flex: 1 1 auto;
   		overflow-y: auto; 
		display: flex;
		flex-direction: column;
	}

	#file_explorer_box .listitem {
		display: flex;
		padding: 5px;
		border-bottom: 1px solid var(--textcolor);
		cursor: pointer;
		flex-direction: column;
	}


	#file_explorer_box .img_preview {
		width: 50px;
		height: 50px;
		object-fit: contain;
		background-color: var(--textcolor);
		margin-right: 10px;
		flex-shrink: 0;
	}

	#file_explorer_box .listitem[data-selected="true"] {
		background-color: var(--textcolor);
		color: var(--bgcolor);
	}

	/* Hover effect but not when selected */
	#file_explorer_box .listitem:not([data-selected="true"]):hover {
		background-color: var(--footer-bg-color);
		color: var(--textcolor);
	}
	#file_explorer_box .bin_preview {
		width: 50px;
		height: 50px;
		background-image: url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24px" height="24px"%3E%3Cpath d="M 6 2 C 4.9057453 2 4 2.9057453 4 4 L 4 20 C 4 21.094255 4.9057453 22 6 22 L 18 22 C 19.094255 22 20 21.094255 20 20 L 20 8 L 14 2 L 6 2 z M 6 4 L 13 4 L 13 9 L 18 9 L 18 20 L 6 20 L 6 4 z"/%3E%3C/svg%3E');
		background-size: 20px;
    	background-repeat: no-repeat;
		background-position: center;
	}
	body.dark #file_explorer_box .bin_preview {
		filter: invert(1);
	}
	/*dont invert if parent is selected and dark mode*/
	body.dark #file_explorer_box .listitem[data-selected="true"] .bin_preview {
		filter: unset;
	}
	/* do invert if parent is selected and light mode*/
	body #file_explorer_box .listitem[data-selected="true"] .bin_preview {
		filter: invert(1);
	}
	#file_explorer .bottom_bar {
		display: flex;
		justify-content: flex-end;
	}

	/* ---- Mid size screens ---- */
		@media only screen and (min-width: 600px) {
		#file_explorer_box {
			width: 600px;
			max-width: 80%;
			height: 60%;
		}
		
		#file_explorer_box .listitem {
			display: flex;
			padding: 5px;
			border-bottom: 1px solid var(--textcolor);
			cursor: pointer;
			justify-content: space-between;
			align-items: center;
			flex-direction: row;
		}

		/* add space between children */
		#file_explorer_box .listitem > * {
			margin: 5px;
		}

	}


  </style>
</head>

<body class="pg-flexbox">
<!-- All content goes in here besides footer.
     Expands to fill in empty space even with no content -->
<div class="pg-flexbox-content">
    <div class="container"  id="post_edit">
	
      <!-- Header -->
      <div style="height: 69px;border-bottom: 1px silver solid;;display: flex;justify-content: space-between;align-items: center;">
        <div>
		<span style="margin-right:10px;filter: grayscale(100%);"><a href="index.php">🏠</a></span>
		<?php 
			echo '<span>Editing: ' . $post_details['post_id'] . '</span>';
		?>
		</div>
        <label id="lightdark-container">
          <input type="checkbox" id="lightdark-checkbox">
          <div id="lightdark-btn"></div>
        </label>
      </div>
	
		<!-- Title goes here -->
		<div class="col s12">
        <textarea id="post_title" name="post_title" style="display:block;width:100%;" placeholder="Title"><?php echo $post_details['title']; ?></textarea>
		</div>
		
		<!-- Main editor -->
		<!-- Expands to take up remaining pg space at minimum -->
		<!-- "flex-grow:1" also put in .note-frame style. -->
        <div id="summernote" style="flex-grow:1;"><?php echo $post_details['content']; ?></div>

		<?php
			echo '<a href="/post/' .$post_details['post_id'] . '" target="_blank">View Live</a>';
		?>
		
		<!-- Tags here -->
		<div class="col s12">
        <input type="text" id="post_tags" name="post_tags" style="width:100%;" placeholder="Tags" value="<?php echo $post_details['tags']; ?>"><br>
		</div>
		
		
		<div class="col s12">
		
		<!-- Post date goes here -->
        <input type="datetime-local" id="post_date" name="post_date"value="<?php echo date('Y-m-d\TH:i:s',$post_details['date']) ?>">
		
		<!-- Post Status [Publishe | Draft] -->
		<label for="post_status" style="display:inline;">
          <input type="checkbox" id="post_status" style="display:none;" <?php if ($post_details['status'] === 'published') echo "checked"; ?>>
          <div id="publish_btn"></div>
        </label>

		
		</div>
		

		
		<!-- Files | Save | Delete buttons -->
		<div class="bottom-row">
			<div id="status_bar">
				<div id="curr_element"></div>
				<div id="word_count"></div>
				<div id="general_msg"></div>
			</div>
			<di id="file_bar">
				<input type="button" id="files_button" value="Files">
				<input type="button" onclick="save()" value="Save Edit">
				<input type="button" onclick="delete_post()" value="DELETE" style="background-color: #6a0909;color: white;">
			</div>
		</div>
	  


    </div>
</div>
    
<!-- Footer -->
<footer class="pg-flexbox-foot">
    <div class="container" id="footnote">
        <p style="text-align: center;"> ͡❛ ͜ʖ ͡❛</p>
	</div>
</footer>

<!-- File Explorer -->
<div id="file_explorer_container" style="display:inherit;">
	<div id="file_explorer_box">
		<div class="upper_bar">
			<div class="title">File Explorer</div>
			<div class="close_btn">X</div>
		</div>
		<div class="progressbar_container">
			<div id="file-progressbar"></div>
		</div>
		<div id="file_list"></div>
		<div class="bottom_bar">
			<input type="button" value="Upload" id="upload_button">
			<input type="button" value="Delete" id="delete_button" disabled="true">
			<input type="button" value="Rename" id="rename_button" disabled="true">
		</div>
	<div>
</div>

  <script src="/js/color-mode.js"></script>

<!--- File Explorer JS -->
<script>
	var post_id = "<?php echo $post_details['post_id']; ?>";
	var backend = "file_backend.php";
	var max_upload_size = 0;

	// Post to backend to get files info
	function get_files_info() {
		fetch(backend + "?op=filesinfo&key=" + post_id)
			.then(response => response.json())
			.then(data => {
				insert_files(data);	
			})
			.catch((error) => {
				console.error('Error:', error);
			});
	}

	// Rename selected file
	function rename_file() {
		var selected = document.querySelector('.listitem[data-selected="true"]');
		if (selected) {
			var filename = selected.getAttribute('data-filename');
			var newname = prompt("Enter new name for " + filename);
			if (newname) {
				fetch(backend + "?op=rename&key=" + post_id + "&filename=" + filename + "&newname=" + newname, {
					method: 'PUT'
				})
				.then(response => response.text())
				.then(data => {
					console.log(data);
					get_files_info();
				})
				.catch((error) => {
					console.error('Error:', error);
				});
			}
		}
	}

	// Delete selected file from backend
	function delete_file() {
		// Confirm delete
		if (!confirm("Are you sure you want to delete this file?")) {
			return;
		}
		var selected = document.querySelector('.listitem[data-selected="true"]');
		if (selected) {
			var filename = selected.getAttribute('data-filename');
			fetch(backend + "?op=delete&key=" + post_id + "&filename=" + filename, {
				method: 'DELETE'
			})
			.then(response => response.text())
			.then(data => {
				console.log(data);
				get_files_info();
			})
			.catch((error) => {
				console.error('Error:', error);
			});
		}
	}

	//gets max upload size from server
	function read_max_upload_size() {
		console.log("Getting max size from", backend + "?op=getmaxsize");
		fetch(backend + "?op=getmaxsize")
			.then(response => response.json())
			.then(data => {
				var max_size = parseInt(data);
				max_upload_size = max_size;
			})
			.catch((error) => {
				console.error('Error getting max size:', error);
			});
	}


	// Upload file to backend.
	// Open system file selector and upload file.
	function upload_file() {
		var file = document.createElement('input');
		file.type = 'file';
		file.onchange = function() {
			// Check if file size is within limits
			if (file.files[0].size > max_upload_size) {
				alert("File size exceeds maximum limit of " + readable_size(max_upload_size));
				return;
			}
			var progressBar = document.getElementById('file-progressbar');
			progressBar.style.visibility = 'visible';
			var formData = new FormData();
			formData.append('file', file.files[0]);

			var xhr = new XMLHttpRequest();
			xhr.open('POST', backend + "?op=upload&key=" + post_id);

			xhr.upload.onprogress = function(event) {
				if (event.lengthComputable) {
					var percent = (event.loaded / event.total) * 100;
					progressBar.style.width = percent + '%';

					if (percent === 100) {
						// width 0% after 2 seconds
						setTimeout(function() {
							progressBar.style.width = '0%';
							progressBar.style.visibility = 'hidden';
						}, 2000);
					}
				}
			};

			xhr.onload = function() {
				if (xhr.status === 200) {
					get_files_info();
				} else {
					console.error('Error:', xhr.statusText);
				}
			};

			xhr.onerror = function() {
				console.error('Error:', xhr.statusText);
			};

			xhr.send(formData);
		};
		file.click();
	}


	function readable_size(size) {
		// Convert bytes to human readable size
		// https://stackoverflow.com/a/20732091
		var i = Math.floor( Math.log(size) / Math.log(1024) );
		return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
	}

	function is_picture(name) {
		// Check if file is an image
		var ext = name.split('.').pop();
		var img_exts = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
		return img_exts.includes(ext);
	}

	function insert_files(data) {
		// Insert files into the file explorer as a tanle
		elem_id = "file_list"
		var elem = document.getElementById(elem_id);
		elem.innerHTML = "";
		for (var i = 0; i < data.length; i++) {
			var img = "";
			if (is_picture(data[i].name)) {
				img = "<img class='img_preview' src='/cms/post_files/" + post_id + "/" + data[i].name + "'>";
			} else {
				img = "<div class='bin_preview'></div>";
			}
			elem.innerHTML = elem.innerHTML + "<div class='listitem' data-selected='false' data-filename='" + data[i].name + "'>"
			+ img +
			"<div>" + data[i].name + "</div>" + 
			"<div>" + readable_size(data[i].size) + "</div>" +
			"<div>" + data[i].date + "</div></div>";

		}
		make_selectable();
	}

	
	function close_explorer() {
		document.getElementById("file_explorer_container").style.visibility = "hidden";
		// Disable buttons
		document.getElementById("delete_button").disabled = true;
		document.getElementById("rename_button").disabled = true;
	}

	function open_explorer() {
		document.getElementById("file_explorer_container").style.visibility = "visible";
		get_files_info();
	}

	// Make file list items selectable
	function make_selectable() {
		var listitems = document.querySelectorAll('.listitem');
		listitems.forEach(function(item) {
			item.addEventListener('click', function() {
				// Deselect all
				listitems.forEach(function(item) {
					item.setAttribute('data-selected', 'false');
				});
				// Select this
				this.setAttribute('data-selected', 'true');
				// Enable buttons
				document.getElementById("delete_button").disabled = false;
				document.getElementById("rename_button").disabled = false;
			});
		});
	}


	// Add event listeners when document is loaded
	document.addEventListener('DOMContentLoaded', function() {
		get_files_info();
		read_max_upload_size();

		// Add event listener to close file explorer
		document.querySelector('.close_btn').addEventListener('click', close_explorer);

		// close file explorer if container clicked but not its children
		document.getElementById("file_explorer_container").addEventListener('click', function(e) {
			if (e.target === this) {
				close_explorer();
			}
		});

		// Add event listener to upload button
		document.getElementById("upload_button").addEventListener('click', upload_file);

		// Add event listener to delete button
		document.getElementById("delete_button").addEventListener('click', delete_file);

		// Add event listener to files button
		document.getElementById("files_button").addEventListener('click', open_explorer);

		// Add event listener to rename button
		document.getElementById("rename_button").addEventListener('click', rename_file);
	});
</script>
  <!-- Initialize text editor --->
   
  <script>
	// ==========================================
	//       Custom Buttons (before init)
	// ==========================================
	// Button to generate outline
	var outline_btn = function(context) {
		var ui = $.summernote.ui;
		var button = ui.button({
			contents: 'Outline',
			tooltip: 'Generate Outline',
			click: function () {
				generate_outline();
			},
			container : $(".note-editor.note-frame") // prevent on-top error
		});
		return button.render();
	};
	// Button to insert sidenotes/margin notes
	var sidenote_btn = function(context) {
        var ui = $.summernote.ui;
		console.log(context);
        var sidenote_menu = ui.buttonGroup([
            ui.button({
                contents: '[i] <span class="note-icon-caret"></span>',
                tooltip: 'Insert sidenote or margin note',
                data: {
                    toggle: 'dropdown'
                },
				container : $(".note-editor.note-frame")
            }),
            ui.dropdown({
                items: [
                    'Sidenote', 'Margin Note'
                ],
				click: function (e) {
					e.preventDefault(); // stop jumping in page
					var btn = e.target.getAttribute('data-value'); // 'Sidenote' or 'Margin Note'
					insert_note(btn);
				},
				container : $(".note-editor.note-frame")
            })
        ]
		);

        return sidenote_menu.render();
	};

	// ==========================================
	//         Setup Summernote Editor
	// ==========================================
	$('#summernote').summernote({
		placeholder: 'Write post here...',
		// Which html tags to show. Deleted h1.
		styleTags: [
		'h2', 'h3', 'h4', 'h5','p','pre', 
		//Custom tags like this
		{title: 'Blockquote', tag: 'blockquote', value: 'blockquote'}
		],
		// Which toolbar buttons to show
		toolbar: [
		  ['style', ['style']],
		  ['font', ['bold', 'underline', 'clear']],
		  ['fontname', ['fontname']],
		  ['color', ['color']],
		  ['para', ['ul', 'ol', 'paragraph']],
		  ['table', ['table']],
		  ['insert', ['link', 'picture']],
		  ['custom', ['outline_btn', 'sidenote_btn']],
		  ['help', ['help']],
		  ['view', ['codeview']]
		],
		buttons: {
			'outline_btn': outline_btn,
			'sidenote_btn': sidenote_btn
		},
		// Include CodeMirror as default src code editor.
		// Much nicer than default editor. Syntax highlighting etc.
		codemirror: {
          mode: 'text/html',
          htmlMode: true,
          lineNumbers: true,
          theme: 'blackboard'
        },
		codeviewFilter: false, // allow script tags
		callbacks: {
			// Add upload event handler.
			// [0] because only supports 1 at a time.
			onImageUpload: function(images) {
				upload_image(images[0]);
			},

			// merge paste formatting
    	    onPaste: function (e) {
				e.preventDefault(); // Prevent default paste behavior

				// Access clipboard data as HTML (or plain text if HTML is unavailable)
				let clipboardData = (e.originalEvent || e).clipboardData || window.clipboardData;
				let bufferHTML = clipboardData.getData('text/html') || clipboardData.getData('text/plain');

				if (bufferHTML) {
					// Sanitize the HTML content
					let sanitizedHTML = removeInlineStyling(bufferHTML);

					// Insert the sanitized HTML
					setTimeout(function() {
						document.execCommand('insertHTML', false, sanitizedHTML);
					}, 10);
				}
			}
		},
		tableClassName:'',
		disableResizeEditor: true,
		spellCheck: true
	});

	// Apply same style class on edit page as the live one
	$('.note-editable').addClass('article');
	
	// ==========================================
	
	// ==========================================
	//         Remove Paste Inline Styling
	// ==========================================
	function removeInlineStyling(htmlContent) {
		// Create a temporary container to parse HTML
		let container = document.createElement('div');
		container.innerHTML = htmlContent;

		// Function to recursively clean HTML elements
		function cleanElement(element) {
			if (element.nodeType === Node.ELEMENT_NODE) {
				// If it's a <pre> tag, keep only its text content
				while (element.attributes.length > 0) {
						element.removeAttribute(element.attributes[0].name);
					}
				// Recursively clean child elements
				Array.from(element.children).forEach(cleanElement);

				// Remove sub-tags and keep text only if it's a <pre> tag
				if (element.tagName.toLowerCase() === 'pre') {
					element.innerHTML = element.textContent;
				}
			}
		}
		// Apply cleaning to all elements within the container
		Array.from(container.children).forEach(cleanElement);

		return container.innerHTML;
	}
	
	// ==========================================
	//         Unsaved Changes Management
	// ==========================================
	// Check if content is changed. Default is true.
	// Set (tuned true) in save()
	var saved = true;
	// 'saved' set to false if following object values are changed.
	// Summernote content is changed
	$('#summernote').on('summernote.change', function(we, contents, $editable) {
		content_changed();
	});
	// Any other of the form object is changed
	var post_elem_ids = ['post_title', 'post_tags','post_date','post_status'];
	for (var i = 0; i < post_elem_ids.length; i++) {
		var elem = document.getElementById(post_elem_ids[i]);
		elem.addEventListener('input', content_changed); 
	}
	// Set saved status untrue and show edited message.
	function content_changed() {
		notify("unsaved *");
		saved = false;
	}
	// On page close check if there is unsaved content and prompt the user.
	window.onbeforeunload = function(){
		if (saved === false) {
			return 'Are you sure you want to discard changes?';
		}
	};
	// ==========================================
	
	
	// ==========================================
	//         Prettify Source Code
	// ==========================================
	// Use Prettier plugin to format the summernote's 
	// cluttered html code in the codemirror.
	function formatHTML() {
		var e = document.querySelector('.CodeMirror');
		// Check if codeview was toggled 'into' not 'out of'
		// CodeMirror obj only exists if codeview was toggled into.
		if (e != null) {
			console.log("Prettifying...");
			$('#summernote').summernote('saveRange');
			c = e.CodeMirror;
			var uglyHTML = c.getValue();
			var prettyHTML = prettier.format(uglyHTML, {
				parser: "html",
				plugins: prettierPlugins,
			});
			c.setValue(prettyHTML);
		} else {
			$('#summernote').summernote('restoreRange');
		}
	}
	// Event: When codeview is toggled.
	$('#summernote').on('summernote.codeview.toggled', function() {
	  formatHTML();
	  scrollToLast();
	});
	// ==========================================
	
	// ==========================================
	//         Scroll to Last Position
	// ==========================================
	let last_pos = 0;
	let selected_text = "";

	document.documentElement.style.scrollBehavior = "auto";

	// whenver key press, mouse up, save the last position
	$('#summernote').on('summernote.keyup', function() {
		last_pos = document.documentElement.scrollTop;
		selected_text = $('#summernote').summernote('editor.getLastRange').sc.textContent;
		console.log(last_pos);
	});
	$('#summernote').on('summernote.mouseup', function() {
		last_pos = document.documentElement.scrollTop;
		selected_text = $('#summernote').summernote('editor.getLastRange').sc.textContent;
		console.log(last_pos);
	});

	function scrollToLast() {
		// check if toggling into codeview or out of it
		var e = document.querySelector('.CodeMirror');
		if (e != null) {
			// toggling into codeview, search selected text
			const c = document.querySelector(".CodeMirror").CodeMirror;
			const cursor = c.getSearchCursor(selected_text);

			if (cursor.findNext()) {
				// scroll and select the text
				c.scrollIntoView(cursor.from(), 100); 
				c.setSelection(cursor.from(), cursor.to());
			}
		} else {
			// toggling out of codeview, scroll to last position
			document.documentElement.scrollTop = last_pos;
		}
	}
	// ==========================================
	
	// ==========================================
	//                Save Post
	// ==========================================
	function save() {
		notify("Saving...");
		// Covers both "saving" of already existing one
		// and creating of new one depending upon page URL
		const url = "?";
		
		var post_status = "";
		var is_pub = document.getElementById("post_status").checked;
		if (is_pub === true) {
			post_status = "published";
		} else {
			post_status = "draft";
		}
		
		// Prettify HTML last time, before saving
		ugly_content = $('#summernote').summernote('code');
		pretty_content = prettier.format(ugly_content, {
				parser: "html",
				plugins: prettierPlugins,
		});
		
		const data = {
		  title: document.getElementById("post_title").value,
		  date: document.getElementById("post_date").value,
		  content: pretty_content,
		  status: post_status,
		  tags: document.getElementById("post_tags").value
		};
		const encodedData = new URLSearchParams(data).toString();
		fetch("", {
		  method: "POST",
		  redirect: 'follow',
		  headers: {
			"Content-Type": "application/x-www-form-urlencoded"
		  },
		  body: encodedData
		})
		  .then((response) => {
			  console.log(response);
			  if (response.redirected === true) {
				notify('Saved');
				// Set global saved status to true.
				saved = true;
				window.location.href = response.url;
			  }

		  })
		  .then((data) => {
			  notify('Saved');
			  // Set global saved status to true.
			  saved = true;
		  })
		  .catch((error) => notify(error));
	}
	// Also capture CTRL+S to save the post using that
	document.addEventListener('keydown', e => {
	  if (e.ctrlKey && e.key === 's') {
		// Prevent the Save dialog to open
		e.preventDefault();
		// Save Post
		save();
	  }
	});
	// ==========================================
	
	
	// ==========================================
	//              Delete Post
	// ==========================================
	function delete_post() {
		var yesno = confirm("Are you sure you want to delete this post?");
		if (yesno) {
			notify("Deleting Post...");
			// send HTTP DELETE request to the current page
			fetch("", {
			  method: "DELETE",
			  redirect: 'follow',
			  headers: {
				"Content-Type": "application/x-www-form-urlencoded"
			  }
			})
			  .then((response) => {
				  console.log(response);
				  console.log(response.url);
				  if (response.redirected === true) {
					window.location.href = response.url;
				  }

			  })
			  .then((data) => notify("Post Deleted"))
			  .catch((error) => notify(error));
		}
	}
	// ==========================================
	
	// ==========================================
    //      Upload Inserted images
	// ==========================================
	function upload_image(image) {
	var post_id = "<?php echo $post_details['post_id']; ?>";
	notify("Uploading image...");
	data = new FormData();
	data.append("file", image);
	$.ajax({
	  data: data,
	  type: "POST",
	  url: "file_backend.php?op=upload&key=" + post_id,
	  cache: false,
	  contentType: false,
	  processData: false,
	  success: function(resp) {
			notify("Image uploaded");
			// use the initial file name it isn't changed
			var url = "/cms/post_files/" + post_id + "/" + data.get("file").name;
			var image = $('<img>').attr('src', url);
			$('#summernote').summernote("insertNode", image[0]);
		},
	  error: function(data) {
			notify(data);
		}
	});
	}
	// ==========================================
	
	// ==========================================
	//         Show Status Messages
	// ==========================================
	function notify(message) {
		general_msg = document.getElementById("general_msg");
		general_msg.innerHTML = message
	}
	// show current focused element tag type
	function show_focused() {
		var curr_element = window.getSelection().focusNode.parentElement;
		curr_element = '<' + curr_element.tagName.toLowerCase() + '>';
		document.getElementById("curr_element").innerText = curr_element + " |";
	}
	document.addEventListener('selectionchange', show_focused);
	// show word count
	function show_word_count() {
		var content = $('#summernote').summernote('code');
		var word_count = content.replace( /[^\w ]/g, "" ).split( /\s+/ ).length;
		document.getElementById("word_count").innerText = word_count + " words" + " |";
	}
	// when content is changed
	$('#summernote').on('summernote.change', function() {
		show_word_count();
	});

	// ==========================================


	// ==========================================
	//         Generate Outline
	// ==========================================
	// Generate outline from headings
	function generate_outline() {
		// Get all headings inside the editor
		var headings = document.querySelectorAll('.note-editable h2, .note-editable h3, .note-editable h4, .note-editable h5');


		// helpers variables and function for list generation
		var level = [null, null, null, null];
		var last_level = -1
		function collect_sublists(low, high) {
			for (var i = high; i > low; i--) {
				// get last element of i-1
				var li = level[i-1].lastElementChild;
				li.appendChild(level[i]);
				level[i-1].appendChild(li);
			}
		}
		function get_cur_prefix() { // heading index like 1.2.3
			var prefix = "";
			for (var i = 0; i <= last_level; i++) {
				prefix += level[i].childElementCount;
				if (i != last_level) {
					prefix += ".";
				}
			}
			return prefix + ".";
		}
		function get_prefix_and_heading(raw_heading) { // always returns heading without prefix even if it has one
			// match using regex, if already has a prefix replace it
			let regex = /^\d+(\.\d+)*\.?\s*/;
			let heading_without_prefix = '';
			if (regex.test(raw_heading)) {
				heading_without_prefix = raw_heading.replace(regex, '');
			} else {
				heading_without_prefix = raw_heading;
			}
			return {'prefix': get_cur_prefix(), 'heading': heading_without_prefix};
		}

		// Over all headings
		headings.forEach(function(heading) {
			// Get heading level
			var h_level = parseInt(heading.tagName[1]) - 2; // h2 -> 0, h3 -> 1, h4 -> 2, h5 -> 3
			var li = document.createElement('li'); // heading goes in here
			console.log(heading.tagName + " " + heading.textContent);

			// Calculate the outline number
			if (h_level > last_level) { // sub heading
				last_level++;
				if (h_level != last_level) {
					// Something went wrong, skipped a level
				}
				// Add new ol
				var ol = document.createElement('ol');
				level[h_level] = ol;
			} else if (h_level < last_level) { // parent heading
				// go back to parent ol while adding finished sublists to parent ol
				collect_sublists(h_level, last_level);
				last_level = h_level;
			}

			// set text
			level[h_level].appendChild(li);
			var prefix_and_heading = get_prefix_and_heading(heading.textContent);
			heading_with_prefix = prefix_and_heading['prefix'] + " " + prefix_and_heading['heading'];
			li.textContent = heading_with_prefix;
			heading.textContent = heading_with_prefix;
		});
		// No more headings, add remaining ol to parent ol
		collect_sublists(0, last_level);

		
		var outline = document.querySelectorAll('.note-editable #outline')[0];
		// Check if outline already exists
		if (outline) {
			outline.innerHTML = "";
		} else {
			// create outline
			outline = document.createElement('div');
			outline.id = 'outline';
			outline.classList.add('outline');
			$('#summernote').summernote('insertNode', outline);
		}

		// Add first ol to outline
		outline.appendChild(level[0]);
		console.log(outline);
	}

	// ==========================================
	// 	   Insert Sidenotes/Margin Notes
	// ==========================================
	function insert_note(type) {
		let random_id = Math.random().toString(36).substring(7);

		// Show number for sidenotes, a fixed symbol for margin notes in text
		let inline_symbol = type === 'Sidenote' ? ' sidenote-number' : ' .marginnote-symbol';
		// Show number within sidenote, nothing for margin note in margin
		let note_type = type === 'Sidenote' ? 'sidenote' : 'marginnote';
		let html = `
		  	<label for="${random_id}" class="margin-toggle${inline_symbol}"></label>
			<input type="checkbox" id="${random_id}" class="margin-toggle" />
			<span class="${note_type}">
				Placeholder text for new margin
			</span>
		`;
		$('#summernote').summernote('pasteHTML', html);
	}
  </script>
  
  
	<!--- Post HTML beautifier -->
  	<script src="https://unpkg.com/prettier@2.8.3/standalone.js"></script>
	<script src="https://unpkg.com/prettier@2.8.3/parser-html.js"></script>


</body>

</html>