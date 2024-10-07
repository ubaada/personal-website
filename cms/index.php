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
$stmt = $auth_pdo->prepare('SELECT * FROM posts WHERE username = ? ORDER BY date DESC');
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
  <link href="/css/common.css" rel="stylesheet">
  <style>
    input[type="submit"] {
      background-color: var(--bgcolor);
      color: var(--textcolor);
      padding: 10px 20px;
      border: 1px solid var(--textcolor);
      cursor: pointer;
      font-size: 1em;
    }

    .tiles {
      display: flex;
      justify-content: space-around;
      gap: 20px;
      justify-content: flex-end;
    }

    .tiles>* {
      padding: 30px;
      margin: 20px 0;
      background-color: var(--bg-shade-1);
      max-width: 200px;
      color: var(--textcolor);
    }

    .tiles>*.clickable {
      cursor: pointer;
      transition: box-shadow 0.2s;
    }

    .tiles>*.clickable:hover {
      box-shadow: 4px 6px 0px 0px var(--textcolor);
    }
  </style>
  <script>
    function new_post() {
      // Send empty POST request to edit.php and follow redirect
      var form = document.createElement('form');
      form.method = 'post';
      form.action = 'edit.php';
      document.body.appendChild(form);
      form.submit();
    }
  </script>
</head>

<body class="pg-flexbox">
  <!-- All content goes in here besides footer.
     Expands to fill in empty space even with no content -->
  <div class="pg-flexbox-content">
    <div class="container">
      <!-- Header -->
      <div
        style="height: 69px;border-bottom: 1px silver solid;;display: flex;justify-content: space-between;align-items: center;">
        <div> CMS: Post Management </div>
        <label id="lightdark-container">
          <input type="checkbox" id="lightdark-checkbox">
          <div id="lightdark-btn"></div>
        </label>
      </div>
      <br />

      <div class="tiles">
        <div>Total Views
          <?php
          $total_views = 0;
          foreach ($all_posts as $post) {
            $views = ($post['views'] === NULL) ? 0 : $post['views'];
            $total_views += $views;
          }
          echo $total_views;
          ?>
        </div>
        <div>Total Posts
          <?php
          echo count($all_posts);
          ?>
        </div>

        <a href="/" class="clickable">Home</a>

        <div class="clickable" onclick="new_post()">
          New Post +
        </div>

      </div>
      <?php
      $tbl_html = "<table><tr><th>Title</th><th>Live Link</th><th>Date</th><th>Views</th></tr>";
      foreach ($all_posts as $post) {
        $title = ($post['title'] == '' || $post['title'] === NULL) ? '(Untitled)' : $post['title'];
        $editlink = '<a href="edit?key=' . $post['post_id'] . '">' . $title . '</a>';
        $viewtext = ($post['status'] == 'published') ? 'View Live ➔' : 'Unpublished Preview ➔';
        $viewlink = $viewlink = '<a href="/post/' . $post['post_id'] . '" target="_blank">' . $viewtext . '</a>';
        $pDate = date('d-m-Y', $post["date"]);
        $views = ($post['views'] === NULL) ? 0 : $post['views'];

        // Concateneate editlink, viewlink, pDate, views into a table row
        $tbl_html = $tbl_html . '<tr><td>' . $editlink . '</td><td>' .
          $viewlink . '</td><td>' . $pDate . '</td><td>' . $views . '</td></tr>';
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