<?php
// ========================================================
//
//         Sends A Public Post on /post/*
//
// ========================================================
if (isset($_GET['key'])) { 
	// DB connection
	$pdo = new PDO('sqlite:../data.db');
	
	// Get post (if its published)
	$stmt = $pdo->prepare('SELECT * FROM posts WHERE post_URL = ? AND status=?');
	$stmt->execute([$_GET['key'],'published']);
	$post_details = $stmt->fetch();
	
	// Check if post exists. Send 404 if not.
	if (!$post_details) {
		http_response_code(404);
		echo "404";
		//include('my_404.php');
		die();
	}

	// Get author details (manily name)
	$usr_stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
	$usr_stmt->execute([$post_details['user_id']]);
	$usr_details = $usr_stmt->fetch();
	
	$pdo = null;
} else {
	// send 404 for now if key isn't givem.
	// Cam be replaced with a post index pg
	http_response_code(404);
	echo "404";
	//include('my_404.php');
	die();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Tags which *must* come first in the header -->
  <meta charset="utf-8">
  <!--Let browser know website is optimized for mobile-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>
    <?php echo $post_details['title']; ?>
  </title>

  <!-- Description for search results-->
  <!-- <meta name="description" content="Software engineer based in New Zealand."> -->

  <link rel="icon" type="image/x-icon" href="images/favicon.png">

  <!--Let browser know website is optimized for mobile-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Font(s) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300&display=swap" rel="stylesheet">

  <!-- Main css file-->
  <link href="/css/custom.css" rel="stylesheet">


  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-6YFPNXLP2B"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-6YFPNXLP2B');
  </script>
  <style>
  #article-info {
	margin: 10px 0px;
  }
  #article-info span {
	margin-right: 10px;
  }
  #article-author {
	
  }
  #article-date {
	
  }
  </style>
</head>

<body>
  <!-- Entire About page along with picture and introduction -->
  <div>
    <div class="container">
      <!-- Heading and the Light/Dark mode button-->
      <div style="height: 69px;border-bottom: 1px silver solid;;position:relative;">
	  	<div style="padding-top: 24px;">Home</div>
        <label id="lightdark-container" style="transform:translateY(-50%)">
          <input type="checkbox" id="lightdark-checkbox">
          <div id="lightdark-btn"></div>
        </label>
      </div>

        <h1><?php echo $post_details['title']; ?></h1>

		<div id="article-info">
			<span id="article-author"><?php echo $usr_details['display_name'] ?></span>
			<span id="article-date"><?php echo $post_details['date'] ?></span>
			<!-- <label id="lightdark-container"> -->
			  <!-- <input type="checkbox" id="lightdark-checkbox"> -->
			  <!-- <div id="lightdark-btn"></div> -->
			<!-- </label> -->
		</div>
      <div class="article">
		 <?php echo $post_details['content'] ?>
	  </div>
  </div>
  </div>





    <!-- Footer -->
    <footer>
      <div class="container" id="footnote">
        <p>Copyright © Ubaada 2022... ig :P</p>
      </div>
    </footer>


    <!-- For optimised loading -->
    <!--
         <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->


    <!-- Color-mode JS -->
    <script src="/js/color-mode.js"></script>
</body>

</html>