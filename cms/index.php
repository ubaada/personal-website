<?php
// ==== Authenticate Session =============
require('session_auth.php');

// ========================================================
//
//                     CMS Home
//
// ========================================================
// Functions:
// 	List all posts
$stmt = $auth_pdo->prepare('SELECT * FROM posts WHERE username = ?');
$stmt->execute([$session_details['username']]);
$all_posts = $stmt->fetchAll();

$auth_pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Tags which *must* come first in the header -->
    <meta charset="utf-8">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
    <title> CMS: Post Management </title>
    <link rel="icon" type="image/x-icon" href="/images/favicon.png">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Font(s) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300&display=swap" rel="stylesheet">
    <!-- Main css file-->
    <link href="/css/custom.css" rel="stylesheet">
    <style></style>
  </head>
  <body class="pg-flexbox">
    <!-- All content goes in here besides footer.
     Expands to fill in empty space even with no content -->
    <div class="pg-flexbox-content">
      <div class="container">
        <!-- Header -->
        <div style="height: 69px;border-bottom: 1px silver solid;;display: flex;justify-content: space-between;align-items: center;">
          <div> CMS: Post Management </div>
          <label id="lightdark-container">
            <input type="checkbox" id="lightdark-checkbox">
            <div id="lightdark-btn"></div>
          </label>
        </div>
		<br/>
		<p>
			<a href="edit">Create A New Post</a>
		</p>
		<h1>All Posts</h1>
		<?php 
		$tbl_html="<table><tr><th>Title</th><th>Live Link</th><th>Date</th></tr>";
		foreach ($all_posts as $post) {
			  $title = ($post['title'] == '') ? '(no title)' : $post['title'];
			  $editlink = '<a href="edit?key='.$post['post_id'].'">'.$post['title'].'</a>';
			  $viewlink = "(Unpublished)";
			  if ($post['status'] == 'published') {
				  $viewlink = '<a href="/post/'.$post['post_id'].'" target="_blank">View Live ➔</a>';
			  }
			  $pDate = date('d-m-Y', $post["date"]);
			  $tbl_html = $tbl_html . '<tr><td>'.$editlink.'</td><td>'.
				$viewlink.'</td><td>'.$pDate.'</tr>';
				
			}
		$tbl_html = $tbl_html . '</table>';
		echo $tbl_html;
		  
		?>
		
      </div>
    </div>
	
    <!-- Footer -->
    <footer class="pg-flexbox-foot">
      <div class="container" id="footnote">
        <p style="text-align: center;"> ͡❛ ͜ʖ ͡❛</p>
      </div>
    </footer>
	
	<script src="/js/color-mode.js"></script>
	
    <script>
	</script>
	
  </body>
</html>