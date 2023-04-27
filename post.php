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
	$stmt = $pdo->prepare('SELECT * FROM posts WHERE post_id = ? AND status=?');
	$stmt->execute([$_GET['key'], 'published']);
	$post_details = $stmt->fetch();

	// Check if post exists. Send 404 if not.
	if (!$post_details) {
		http_response_code(404);
		echo "404";
		//include('my_404.php');
		die();
	}

	// Get author details (manily name)
	$usr_stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
	$usr_stmt->execute([$post_details['username']]);
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


	<!-- Google Analytics, optimised loading -->
	<script>
		// only load google analytics if not in dev mode
		// dev mode: 
		//     either on localhost (1)
		//     dev=true in localstorage or ?dev=true in url (2)

		var isDev = false;

		// check if hostname contains localhost (1)
		isDev = location.hostname.includes("localhost");
		if (isDev === false) {
			// check if (true|false) dev is in localstorage (2)
			var isDev = localStorage.getItem("dev") !== null;
			if (!isDev) {
				// dev not in localstorage, check if dev=true in url (2)
				var urlSearchParams = new URLSearchParams(window.location.search);
				var params = Object.fromEntries(urlSearchParams.entries());

				// if dev=true in url, set dev=true in localstorage for future use
				if (params.dev) {
					localStorage.setItem("dev", "yes");
					isDev = true;
				}
			}
		}

		// if not in dev mode, load google analytics
		if (isDev === false) {
			var head = document.getElementsByTagName("head")[0];
			var adScript = document.createElement("script");
			adScript.type = "text/javascript";
			var gaID = "UA-114940188-1";
			adScript.src = "https://www.googletagmanager.com/gtag/js?id=" + gaID;
			adScript.async = true;
			head.appendChild(adScript);

			window.dataLayer = window.dataLayer || [];
			function gtag() { dataLayer.push(arguments); }
			gtag('js', new Date());

			gtag('config', gaID);
		} else {
			// dev mode, load google analytics
			console.log("dev mode");
		}
	</script>


	<style>
		#article-info {
			color: var(--footer-txt-color);
			display: flex;
			font-size: 12px;
			align-items: center;

		}

		#article-info span {
			margin-right: 10px;
		}

		#article-author {}

		#article-date {}

		.edit-btn {
			font-size: 20px;
			position: fixed;
			bottom: 10px;
			right: 10px;
			padding: 10px;
			border-radius: 6px;
			color: var(--textcolor);
			box-shadow: 3px 2px 11px 1px;
			filter: grayscale(100%);
			font-size: 16px;
		}

		#lightdark-btn {
			width: unset;
			height: unset;
			font-size: 22px;
			color: unset;
		}

		.post-tags-line {
			color: #7070708a;
			border-top: 1px solid #42424263;
			padding-top: 5px;
		}

		.post-tag {
			border-radius: 7px;
			padding: 3px 8px;
			font-size: 12px;
			color: #727272;
		}

		@media print {

			/* All your print styles go here */
			@page {
				margin: 2cm !important;

				@bottom-right {
					content: counter(page) " of " counter(pages);
				}
			}

			#lightdark-container,
			.post-tags-line,
			.edit-btn,
			footer {
				display: none !important;
			}

			body,
			body.dark {
				--bgcolor: white;
				--textcolor: black;
				--shadow-color: rgb(0 0 0 / 10%);
				--footer-bg-color: #d8d8d89e;
				--footer-txt-color: #9da1a6;
				--accent-color: orange;
				--code-bg-color: #e9e9e9;
				--a-color: #007b8a;
			}

			html {
				line-height: 1.3;
			}

			.article {
				width: 100%;
				font-size: 12pt;
			}

			h1 {
				padding: 1.8rem 0 1.2rem 0;
				font-size: 3.2rem;
			}

			.article,
			h1,
			h2,
			h3,
			h4,
			h5,
			a,
			p a {
				color: black;
				background-color: white;
				line-height: 100%;
			}

			a,
			p a {
				border: none;
				padding: 0px;
			}

			/* Defining all page breaks */
			a {
				page-break-inside: avoid
			}

			blockquote {
				page-break-inside: avoid;
			}

			h1,
			h2,
			h3,
			h4,
			h5,
			h6 {
				page-break-after: avoid;
				page-break-inside: avoid
			}

			img {
				page-break-inside: avoid;
				page-break-after: avoid;
			}

			table,
			pre {
				page-break-inside: avoid
			}

			ul,
			ol,
			dl {
				page-break-before: avoid
			}

			/* Thank you message */
			.article:after {
				border-top: 1px dashed;
				padding-top: 2pt;
				content: "Printed from ubaada.com. Glad you liked it enough to print :)";
			}
		}
	</style>
</head>

<body class="pg-flexbox">
	<!-- Entire About page along with picture and introduction -->
	<div class="pg-flexbox-content">
		<div class="container">
			<h1>
				<?php echo $post_details['title']; ?>
			</h1>

			<div id="article-info">
				<!-- Author name -->
				<a href="/">
					<span id="article-author">
						<?php echo $usr_details['display_name'] ?>
					</span>
				</a>
				<!-- arrow -->
				<span>&gt;</span>
				<!-- Post date -->
				<span id="article-date">
					<?php echo date('d-m-Y', $post_details['date']); ?>
				</span>

				<!-- Edit button if session cookie set -->
				<?php if (isset($_COOKIE["session_token"])): ?>
					<a href="/cms/edit?key=<?php echo $_GET['key'] ?>" target="_blank"><span class="edit-btn">🖊️</span></a>
				<?php endif; ?>

				<label id="lightdark-container">
					<input type="checkbox" id="lightdark-checkbox">
					<div id="lightdark-btn"></div>
				</label>
			</div>
			<div class="article">
				<?php echo $post_details['content'] ?>
				<div class="post-tags-line">
					<span>Tags:</span>
					<?php foreach ($array = explode(',', $post_details['tags']) as $tag): ?>
						<a href=""><span class="post-tag">
								<?php echo $tag; ?>
							</span></a>
					<?php endforeach; ?>
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


	<!-- For optimised loading -->
	<!--
		 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->


	<!-- Color-mode JS -->
	<script src="/js/color-mode.js"></script>
</body>

</html>