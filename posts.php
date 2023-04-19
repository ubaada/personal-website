<?php
// ========================================================
//
//                   All Published Posts
//
// ========================================================
// Functions:
//  List all published posts. This is a public page.
// ========================================================
$pdo = new PDO('sqlite:../data.db');
$stmt = $pdo->prepare('SELECT * FROM posts WHERE status = "published"');
$stmt->execute();
$all_posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Tags which *must* come first in the header -->
    <meta charset="utf-8">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
    <title>My posts</title>
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
          <div>All Posts</div>
          <label id="lightdark-container">
            <input type="checkbox" id="lightdark-checkbox">
            <div id="lightdark-btn"></div>
          </label>
        </div>
		    <br/>
		<?php 
		$tbl_html="<table><tr><th>Title</th><th>Date</th></tr>";
		foreach ($all_posts as $post) {
			  $title = $post['title'];
			  $viewlink = '<a href="/post/'.$post['post_id'].'">' .$post['title']. '</a>';

			  $pDate = date('d-m-Y', $post["date"]);
			  $tbl_html = $tbl_html . '<tr><td>'.$viewlink.'</td><td>'.$pDate.'</tr>';
				
			}
		$tbl_html = $tbl_html . '</table>';
		echo $tbl_html;
		  
		?>
    <!-- Edit button if session cookie set -->
		<?php if (isset($_COOKIE["session_token"])): ?>
			<a href="/cms/index"><span class="edit-btn" style="
      font-size: 20px;position: fixed;bottom: 10px;right: 10px;padding: 10px;
    border-radius: 6px;color: var(--textcolor);box-shadow: 3px 2px 11px 1px;
    filter: grayscale(100%);font-size: 16px;
  ">Manage Posts</span></a>
	  <?php endif; ?>
		<p>
			<a href="/">< Homepage</a>
		</p>
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