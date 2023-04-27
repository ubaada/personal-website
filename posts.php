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
  <style></style>
</head>

<body class="pg-flexbox">
  <!-- All content goes in here besides footer.
     Expands to fill in empty space even with no content -->
  <div class="pg-flexbox-content">
    <div class="container">
      <!-- Header -->
      <div
        style="height: 69px;border-bottom: 1px silver solid;;display: flex;justify-content: space-between;align-items: center;">
        <div>All Posts</div>
        <label id="lightdark-container">
          <input type="checkbox" id="lightdark-checkbox">
          <div id="lightdark-btn"></div>
        </label>
      </div>
      <br />
      <?php
      $tbl_html = "<table><tr><th>Title</th><th>Date</th></tr>";
      foreach ($all_posts as $post) {
        $title = $post['title'];
        $viewlink = '<a href="/post/' . $post['post_id'] . '">' . $post['title'] . '</a>';

        $pDate = date('d-m-Y', $post["date"]);
        $tbl_html = $tbl_html . '<tr><td>' . $viewlink . '</td><td>' . $pDate . '</tr>';

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
        <a href="/">
          < Homepage</a>
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