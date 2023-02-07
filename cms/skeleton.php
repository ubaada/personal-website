<?php
// ==== Authenticate Session =============
require('session_auth.php');

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Tags which *must* come first in the header -->
    <meta charset="utf-8">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
    <title> Title </title>
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
      <div class="container" id="post_edit">
        <!-- Header -->
        <div style="height: 69px;border-bottom: 1px silver solid;;display: flex;justify-content: space-between;align-items: center;">
          <div> Title </div>
          <label id="lightdark-container">
            <input type="checkbox" id="lightdark-checkbox">
            <div id="lightdark-btn"></div>
          </label>
        </div>
		
		<!-- Rest of the content for body -->
		
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