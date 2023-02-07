<?php
// ==== Authenticate Session =============
require('session_auth.php');

// ========================================================
//
//            Create and Edits Posts
//
// ========================================================
//	Access Pattern:
//	1. /		:	[GET] 	Display the new post page.
// 							Shows Create btn but not Save.
//	   				[POST] 	Create new post from POST
//							and redirect to new post.
//	2. if /key	:	[GET]	Load stored data into page WHERE key.
//							Show the Save button.
//					[POST]	Update post from POST data.
//							Reidrect to GET.
//                 [DELETE]	Delete current post.
//                          Redirect to /edit home.

// Placeholder post variable
date_default_timezone_set('Pacific/Auckland');
$post_details = array("post_id"=>"",
						"user_id"=>"",
						"post_URL"=>"",
						"title"=>"",
						"date"=>time(),
						"content"=>"",
						"status"=>"draft",
						"tags"=>"",
						"views"=>""
						);

$edit_mode = false; // bool: Is it editing an exisiing post?
$error = "";
if (isset($_GET['key'])) { // Indvidual post address /?key=
	$edit_mode = true;
	$post_key = $_GET['key'];
	if ($_SERVER['REQUEST_METHOD']==='POST') {
		// Update existing post
		$dateimestamp = strtotime($_POST["date"]);
		
		//replace old tmp img urls with KEY folder
		[$new_content, $used_tmpfiles] = replace_img_URLs($_POST["content"], $_GET['key']);
		
		$stmt = $auth_pdo->prepare('UPDATE posts SET title = ?, date=?, content=?, status=?,tags=? WHERE post_URL = ?');
		$stmt->execute([$_POST["title"], 
						$dateimestamp, 
						$new_content, 
						$_POST["status"],
						$_POST["tags"],
						$_GET['key']
					]);
		
		if ($stmt){
			// Save successfull
			
			// Check old images that were reused
			// Delete the rest.
			// Reuse unedited content as it came from the editor.
			delete_unused_images($_POST['content'], $_GET['key']);
			// move tmp files if altered URLs were successfully inserted.
			move_tmp_images($used_tmpfiles, $_GET['key']);
		} else {
			// Post did not update.
			http_response_code(404);
			echo "Post update failed.";
			//include('my_404.php');
		}
		die();
	} else if ($_SERVER['REQUEST_METHOD']==='DELETE') {
		// Delete the post
		$stmt = $auth_pdo->prepare('DELETE FROM posts WHERE post_URL=? AND user_id=?');
		$stmt->execute([$_GET['key'],
						$session_details['user_id']
					]);
		
		// Post deleted. Redirect to the edit page
		$newDest = 'index';
		header('Location: '.$newDest);
		exit;
	} else if ($_SERVER['REQUEST_METHOD']==='GET') {
		$stmt = $auth_pdo->prepare('SELECT * FROM posts WHERE post_URL = ? AND user_id = ?');
		$stmt->execute([$post_key, $session_details['user_id']]);
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
		
		// Using base16 because of consistent result length
		// only base(2^n) divide bytes(8bit) evenly.
		// Base36 (26 alph + 10 nums) isn't a power of 2.
		$rand_post_URL = bin2hex(random_bytes(4));
		$dateimestamp = strtotime($_POST["date"]);
		//replace old tmp img urls with KEY folder
		[$new_content, $used_tmpfiles] = replace_img_URLs($_POST["content"], $rand_post_URL);
		
		$stmt = $auth_pdo->prepare('INSERT INTO posts (user_id, post_URL, title, date, content, status, tags) VALUES (?,?,?,?,?,?,?)');
		$stmt->execute([$session_details['user_id'],
						$rand_post_URL, 
						$_POST["title"], 
						$dateimestamp, 
						$new_content, 
						$_POST["status"],
						$_POST["tags"]]
					);
		if ($stmt) {
			// New post inserted with URL rand_post_URL.
			// Safe to move tmp files permanently to a folder.
			// and delete the unused ones.
			move_tmp_images($used_tmpfiles, $rand_post_URL);
		}
		echo "Added? :I";
		// Resource/post created. Redirect to it.
		$newDest = '?key='.$rand_post_URL;
		header('Location: '.$newDest);
	} else if ($_SERVER['REQUEST_METHOD']==='GET') {
		// Display new post page (default)
		$edit_mode = false;
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
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

	<!-- include codemirror (codemirror.css, codemirror.js, xml.js, formatting.js)-->
  <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/codemirror.min.css" />
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/theme/blackboard.min.css">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/theme/monokai.min.css">
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/codemirror.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.41.0/mode/xml/xml.min.js"></script>

  
    <!-- Main css file-->
  <link href="/css/custom.css" rel="stylesheet">

  
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
	
	.submit-row {
		position: sticky;
		bottom: 0px;
		background-color: var(--bgcolor);
		display: flex;
		justify-content: flex-end;
		align-items: center;
		column-gap: 0.875rem;
		border-top: 1px solid var(--footer-bg-color);
	}
	#form_status {
		display: inline;
		margin-right: auto;
	
	}
	
	/* overiding summernote's css */
	.note-frame {
		color: var(--textcolor);
		border:1px solid var(--textcolor) !important;
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
	
	.note-toolbar,a.note-dropdown-item, a.note-dropdown-item:hover, .note-modal-content,.note-modal-title,.note-form-label,.note-dropdown-menu {
		color: var(--textcolor) !important;
		background: var(--bgcolor) !important;
		border: 1px solid var(--bgcolor);
	}
	
	.note-editor .note-editing-area .note-editable a, .note-editor .note-editing-area .note-editable a:hover{
		all:inherit;
		
	}
	.note-editing-area {
		flex-grow:1;
	}
	.note-editable {
		height: 100%;
	}
	/* Overide article container size in editor */
	.article {
		width:unset;
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
		if ($edit_mode === true) {	
			echo '<span>Editing: ' . $post_details['post_URL'] . '</span>';
		} else {
			echo '<span>Create New Post</span>';
		}
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
		// If post['status'] is set then it meets its a old post
		// Check to see if its an published old post.
		if ($post_details['status'] === "published") {
			echo '<a href="/post/' .$post_details['post_URL'] . '" target="_blank">View Live</a>';
		}
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
		

		
		<!-- Create New | Save | Delete buttons -->
		<div class="col s12 submit-row">
			<div id="form_status"></div>
			
			<?php if ($edit_mode == true): ?>
			
			<input type="button" onclick="save()" value="Save Edit">
			<input type="button" onclick="delete_post()" value="DELETE" style="background-color: #6a0909;color: white;">
			
			
			<?php else: ?>
			
			<input type="button" onclick="save()" value="Create Post">

			<?php endif; ?>
			
		</div>
	  


    </div>
</div>
    
<!-- Footer -->
<footer class="pg-flexbox-foot">
    <div class="container" id="footnote">
        <p style="text-align: center;"> ͡❛ ͜ʖ ͡❛</p>
	</div>
</footer>

  <script src="/js/color-mode.js"></script>


  <!-- Initialize text editor --->
  <script>
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
		  ['view', ['codeview']],
		],
		// Include CodeMirror as default src code editor.
		// Much nicer than default editor. Syntax highlighting etc.
		codemirror: {
          mode: 'text/html',
          htmlMode: true,
          lineNumbers: true,
          theme: 'blackboard'
        },
		callbacks: {
			// Add upload event handler.
			// [0] because only supports 1 at a time.
			onImageUpload: function(images) {
				upload_image(images[0]);
			}
		}
	});
	// Apply same style class on edit page as the live one
	$('.note-editable').addClass('article');
	// ==========================================
	
	
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
			c = e.CodeMirror;
			var uglyHTML = c.getValue();
			var prettyHTML = prettier.format(uglyHTML, {
				parser: "html",
				plugins: prettierPlugins,
			});
			c.setValue(prettyHTML);
		}
	}
	// Event: When codeview is toggled.
	$('#summernote').on('summernote.codeview.toggled', function() {
	  formatHTML();
	});
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
	notify("Uploading image...");
	data = new FormData();
	data.append("image", image);
	$.ajax({
	  data: data,
	  type: "POST",
	  url: "img_upload.php",
	  cache: false,
	  contentType: false,
	  processData: false,
	  success: function(url) {
			notify("Image uploaded");
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
		form_status.innerHTML = message;
	}
	// ==========================================
  </script>
  
  
	<!--- Post HTML beautifier -->
  	<script src="https://unpkg.com/prettier@2.8.3/standalone.js"></script>
	<script src="https://unpkg.com/prettier@2.8.3/parser-html.js"></script>


</body>

</html>