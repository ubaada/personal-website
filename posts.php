<?php
// ========================================================
//
//                   All Published Posts
//
// ========================================================
// Functions:
//  List all published posts. This is a public page.
//  If 'tags' is set in the url, only posts with that tag are shown.
// ========================================================
$pdo = new PDO('sqlite:../data.db');

$tag_filtered = false;
// Check if tags are specified in the url
if (isset($_GET['tags'])) {
  $tag_filtered = true;
  $tagsInput = $_GET['tags'];
  $tagsArray = explode(',', $tagsInput);

  // Construct the query dynamically based on tags
  $tagsPlaceholder = rtrim(str_repeat('?,', count($tagsArray)), ',');
  $sql = "SELECT * FROM posts WHERE status = 'published' AND (";
  foreach ($tagsArray as $tag) {
    $sql .= "tags LIKE ? AND ";
  }
  $sql = rtrim($sql, " AND "); // Remove the last ' OR'
  $sql .= ") ORDER BY date DESC";

  $stmt = $pdo->prepare($sql);

  // Bind parameters for each tag
  foreach ($tagsArray as $index => $tag) {
    // using binValue instead of string concatenation to prevent SQL injection
    $stmt->bindValue($index + 1, "%{$tag}%", PDO::PARAM_STR);
  }
} else {
  // No tags are specified, prepare a query to get all published posts
  $sql = 'SELECT * FROM posts WHERE status = "published" ORDER BY date DESC';
  $stmt = $pdo->prepare($sql);
}

// Execute the prepared statement and fetch all matching posts
$stmt->execute();
$all_posts = $stmt->fetchAll();

// Calculate a list of all unique tags across all posts
// except the tags that are already in the url if tags are filtered
$all_tags = [];
foreach ($all_posts as $post) {
  $tags = explode(',', $post['tags']);
  foreach ($tags as $tag) {
    $tag = trim($tag);
    if (!in_array($tag, $all_tags) && (!$tag_filtered || !in_array($tag, $tagsArray))) {
      $all_tags[] = $tag;
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
  <link href="/css/common.css" rel="stylesheet">
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
      var gaID = "G-6YFPNXLP2B";
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
    .filter-line {
			color: var(--txt-shade-1);
			padding: 5px 0px;
      margin-bottom: 10px;
		}
		/* style different from /posts.php */
    /* styled like a capsule pill */
		.post-tag {
      background-color: var(--bg-shade-1);
      color: var(---textcolor);
      padding: 5px 10px;
      border-radius: 20px;
      margin-right: 5px;
      margin-bottom: 5px;
      display: inline-block;
      font-size: 14px;
      text-decoration: none;
		}
    /* a cross that removes tag */
    .remove-tag {
      margin-left: 5px;
      background-color: var(--txt-shade-1);
      font-size: 12px;
      width: 12px;
      height: 12px;
      cursor: pointer;
      
      -webkit-mask-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>');
      mask-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/></svg>');

      background-repeat: no-repeat;
      background-size: 12px;
      background-position: center;
      display: inline-block;
      vertical-align: middle;
    }
    /* cross changes color on hover */
    .remove-tag:hover {
      background-color: var(--textcolor);
    }

    .other-tags-container {
      display: inline-block;
      position: relative;;
    }
    .add-tag-button {
      /* round plus button */
      color: var(--txt-shade-1);
      padding: 5px 10px;
      font-size: 14px;
      text-decoration: none;
      cursor: pointer;
    }
    .add-tag-button:hover {
      background-color: var(--bg-shade-1);
      color: var(--textcolor);
    }
    #other-tags {
      position: absolute;
      background-color: var(--bg-shade-1);
      padding: 5px 10px;
      z-index: 1;
      top: 100%;
      display: none;
      }
    .other-tags-option {
      display: block;
      padding: 5px 10px;
      cursor: pointer;
      /* no break on long tags */
      white-space: nowrap;
    }
    .other-tags-option:hover {
      color: var(--textcolor);
    }
    #other-tags.show {
      display: block;
    }
    .add-tag-button.clicked {
      background-color: var(--bg-shade-1);
      color: var(--textcolor);
    }
		

  </style>
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
      <div>
        
      <!-- Display tags if filtered -->
      
            <div class="filter-line">
            <span>Filter(s):</span>
            
            <!-- Display tags that are already in the url -->
            <?php if ($tag_filtered) : ?>
              <?php foreach ($tagsArray as $tag) : ?>
                <?php $tag = trim($tag); ?>
                  <span class="post-tag">
                    <?php echo $tag; ?>
                    <span class="remove-tag" data-tag="<?php echo $tag; ?>"></span>
                  </span>
              <?php endforeach; ?>
            <?php endif; ?>

            <!-- All other tags in a dropdown -->
            <div class="other-tags-container">
              <div id="other-tags">
                <!-- Placeholder string if no tags are present -->
                <?php if (empty($all_tags)) : ?>
                  (No other tags)
                <?php endif; ?>
                <?php foreach ($all_tags as $tag) : ?>
                  <div class="other-tags-option"><?php echo $tag; ?></div>
                <?php endforeach; ?>
                </div>
              <span class="add-tag-button">+</span>
                </div>
            
          </div>
      </div>

      <!-- Display all posts in a table -->
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


<!-- Tag add remove script -->
  <script>
    // Remove tag from url when  .remove-tag is clicked
    // according to its data-tag attribute
    document.querySelectorAll('.remove-tag').forEach(function (tag) {
      tag.addEventListener('click', function () {
        var tag = this.getAttribute('data-tag');
        var tags = new URLSearchParams(window.location.search).get('tags');
        console.log(tags.toString());
        // if tags contain a comma, split it into array and remove only tag
        if (tags.includes(',')) {
          var tagsArray = tags.split(',');
          var newTags = tagsArray.filter(function (t) {
            return t !== tag;
          });
          window.location.search = '?tags=' + newTags.join(',');
        } else {
          window.location.search = '';
        }
      }); 
    });

    // Add tag to url when selected from dropdown
    document.getElementById('other-tags').addEventListener('change', function () {
      var tag = this.value;
      var tags = new URLSearchParams(window.location.search).get('tags');
      if (tags) {
        window.location.search = '?tags=' + tags + ',' + tag;
      } else {
        window.location.search = '?tags=' + tag;
      }
    });

    // Show dropdown when + button is clicked
    document.querySelector('.add-tag-button').addEventListener('click', function () {
      document.getElementById('other-tags').classList.toggle('show');
      this.classList.toggle('clicked');
    });

    // Add tag to url when selected from dropdown
    document.getElementById('other-tags').addEventListener('click', function (e) {
      if (e.target.classList.contains('other-tags-option')) {
        var tag = e.target.textContent;
        var tags = new URLSearchParams(window.location.search).get('tags');
        if (tags) {
          window.location.search = '?tags=' + tags + ',' + tag;
        } else {
          window.location.search = '?tags=' + tag;
        }
      }
    });

    // Hide dropdown when clicked outside of other-tags-container
    document.addEventListener('click', function (e) {
      if (!document.querySelector('.other-tags-container').contains(e.target)) {
        document.getElementById('other-tags').classList.remove('show');
        document.querySelector('.add-tag-button').classList.remove('clicked');
      }
    });
    
  </script>

</body>

</html>