<?php
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

// ========================================================
//	Access Pattern:
//	1. /		:	[GET] 	Display the new post page.
// 							Shows Create btn but not Save.
//	   				[POST] 	Create new post from POST
//							and redirect to new post.
//	2. if /key	:	[GET]	Load stored data into page WHERE key.
//							Show the Save button.
//					[POST]	Update post from POST data
//							and show GET page data

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

$edit_mode = false; // Is it editing an exisiing post
$error = "";
if (isset($_GET['key'])) { // Indvidual post address /?key=
	$edit_mode = true;
	$post_key = $_GET['key'];
	if ($_SERVER['REQUEST_METHOD']==='POST') {
		// Update existing post
		$dateimestamp = strtotime($_POST["date"]);
		
		$stmt = $auth_pdo->prepare('UPDATE posts SET title = ?, date=?, content=?, status=?,tags=? WHERE post_URL = ?');
		$stmt->execute([$_POST["title"], 
						$dateimestamp, 
						$_POST["content"], 
						$_POST["status"],
						$_POST["tags"],
						$_GET['key']
					]);
		
		$post_details = $stmt->fetch();
	}
	$stmt = $auth_pdo->prepare('SELECT * FROM posts WHERE post_URL = ? AND user_id = ?');
	$stmt->execute([$post_key, $session_details['user_id']]);
	$post_details = $stmt->fetch();
} else { // Root / address
	if ($_SERVER['REQUEST_METHOD']==='POST') {
		// Insert new post into db.
		$rand_post_URL = bin2hex(random_bytes(4));
		$dateimestamp = strtotime($_POST["date"]);
		
		$stmt = $auth_pdo->prepare('INSERT INTO posts (user_id, post_URL, title, date, content, status, tags) VALUES (?,?,?,?,?,?,?)');
		$stmt->execute([$session_details['user_id'],
						$rand_post_URL, 
						$_POST["title"], 
						$dateimestamp, 
						$_POST["content"], 
						$_POST["status"],
						$_POST["tags"]]
					);
		
		$post_details = $stmt->fetch();
		echo "Added? :I";
		// Resource/post created. Redirect to it.
		$newDest = '?key='.$rand_post_URL;
		header('Location: '.$newDest);
	}
	
}
$stmt = $auth_pdo->prepare('SELECT post_URL, title, status FROM posts WHERE user_id = ?');
$stmt->execute([$session_details['user_id']]);
$all_posts = $stmt->fetchAll();

$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Tags which *must* come first in the header -->
  <meta charset="utf-8">
  <!--Let browser know website is optimized for mobile-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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


  
    <!-- Main css file-->
  <link href="/css/custom.css" rel="stylesheet">

  
  <style>
    blockquote {
      color: redo;
    }

    .thinform {
      max-width: 100%;
      width: 800px;
      font-size: 25px;
    }

    #post_edit {
      margin: 20px 0px;
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
      display: block;
	  color: var(--textcolor);
	  box-sizing: border-box;
    }
	.post_item {
		padding:10px;
		margin:5px;
		border:1px solid var(--textcolor);
		width:100%;
		display:inline-block;
		overflow:hidden;
		box-sizing: border-box;
	}
	.post_item span {
		float:right;
		clear:both;
		color: var(--textcolor);
	}
	
	/* overiding summernote's css */
	.note-frame {
		color: var(--textcolor);
		border:1px solid var(--textcolor) !important;
	}
	
	.note-toolbar {
		background-color:var(--bgcolor);
		color: var(--textcolor);
	}
	.note-btn {
		background-color:var(--bgcolor);
		color: var(--textcolor);
		border:none !important;
	}
  </style>
</head>

<body>
    <div class="container">
      <!-- Heading and the Light/Dark mode button-->
      <div style="height: 69px;border-bottom: 1px silver solid;;position:relative;">
        <div style="padding-top: 24px;">Home</div>
        <label id="lightdark-container" style="transform:translateY(-50%)">
          <input type="checkbox" id="lightdark-checkbox">
          <div id="lightdark-btn"></div>
        </label>
      </div>
	  
      <div class="row" id="post_edit">

		<div class="col s12">
        <label for="post_title">Title</label>
        <textarea id="post_title" name="post_title" style="display:block;width:100%;"><?php echo $post_details['title']; ?></textarea>
		</div>
		
		<div class="col s12">
        <div id="summernote">
          <?php
		  echo $post_details['content'];
		  ?>
        </div>
		</div>
		
		<div class="col s12">
		<label for="post_date">Post Date:</label>
        <input type="datetime-local" id="post_date" name="post_date"value="<?php echo date('Y-m-d\TH:i:s',$post_details['date']) ?>">
		</div>

		<div class="col s12">
        <label for="post_tags">Tags</label><br>
        <input type="text" id="post_tags" name="post_tags" style="width:100%;" value="<?php echo $post_details['tags']; ?>"><br>
		</div>
		
		<div class="col s12">
        <input type="checkbox" id="post_status" name="post_status" style="display:inline;" <?php if ($post_details['status'] === 'published') echo "checked"; ?>>
        <label for="post_status"> Publish</label><br>
		</div>
		
		<div class="col s12">
		<?php if ($edit_mode == true): ?>
		<input type="button" onclick="save_edit()" value="Save Edit">
		<a href="edit.php">Create another post.</a>
		<?php else: ?>
		<input type="button" onclick="create_post()" value="Create Post">
		<?php endif; ?>
		</div>
		
		<div class="col s12">
		<div id="form_status" style="text-align: right;"></div>
		</div>
		
      </div>
	  
	  <div class="row" id="all-posts">
	  
	  <?php if ($edit_mode == false) {
		  $list_html = "<h2>All Posts</h2>";
		  foreach ($all_posts as $post) {
			  $title = ($post['title'] == '') ? '(no title)' : $post['title'];
			  $star = ($post['status'] == 'published') ? '  ✔' : '';
			  
			  $list_html = $list_html . 
			  '<div class="col s12 m6"><a href="?key='.$post['post_URL']. '">'.
			  '<div class="post_item">' .
			  $title . 
			  '<span>'.$star.'</span>'.
			  '</div></a></div>';
			}
		  echo $list_html;
	  } ?>
	  
	  <!--end-->
	 </div>

    </div>



  <script src="/js/color-mode.js"></script>


  <!-- Initialize text editor --->
  <script>
	$('#summernote').summernote({
		height: 200,
		callbacks: {
			onImageUpload: function(images) {
				upload_image(images[0]);
			}
		}
	});
	var form_status = document.getElementById("form_status");
	function create_post() {
		const url = "?";
		
		var post_status = "";
		var is_pub = document.getElementById("post_status").checked;
		if (is_pub === true) {
			post_status = "published";
		} else {
			post_status = "draft";
		}
		
		const data = {
		  title: document.getElementById("post_title").value,
		  date: document.getElementById("post_date").value,
		  content: $('#summernote').summernote('code'),
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
			  console.log(response.url);
			  if (response.redirected === true) {
				window.location.href = response.url;
			  }

		  })
		  .then((data) => console.log(data))
		  .catch((error) => form_status.innerHTML = error);
	}
	
	function save_edit() {
		form_status.innerHTML = "Saving...";
		create_post();
		form_status.innerHTML = "Saved.";
	}
	
	  function upload_image(image) {
		form_status.innerHTML = "Uploading image...";
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
				form_status.innerHTML = "Image uploaded.";
				var image = $('<img>').attr('src', url);
				$('#summernote').summernote("insertNode", image[0]);
			},
		  error: function(data) {
				form_status.innerHTML =data;
			}
		});
	  }
	  
  </script>
  

</body>

</html>