<?php
// ==== Authenticate Prev Session (if any) =============
if(isset($_COOKIE["session_token"])) {
	// Browser already has session cookie
	// Check if valid
	require('session_auth.php');
	// Session valid: user already signed in.
	// Redirect to edit page
	$newDest = 'index.php';
	header('Location: '.$newDest);
}


function login($input_username, $input_pw) {
	# Check if username exists in table users
	$pdo = new PDO('sqlite:../../data.db');
	$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
	$stmt->execute([$input_username]);

	$user_details = $stmt->fetch();
	if ($user_details) {
		# Username correct, Check password.
		$hash = $user_details['hash_pw_with_salt'];
		$pw_correct = password_verify($input_pw, $hash);
		if ($pw_correct) {
			# Password correct.
			
			# Create Session Token
			$new_token = bin2hex(random_bytes(32));
			
			// Set the TTL interval (in seconds) + current time
			$ttl = 604800;
			$ttl = time() + $ttl;
			
			# Insert session into sessions table
			$stmt_i = $pdo->prepare('INSERT INTO sessions (username, token, ttl) VALUES (?, ?, ?)');
			$stmt_i->execute([$user_details['username'], $new_token, $ttl]);
			
			// Set session cookie
			setcookie("session_token", $new_token, $ttl, "/");
			return true;
			
		} else {
			// print("Incorrect password.");
			return false;
		}
	} else {
		// print("Username not found.");
		return false;
	}
}
$login_status = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$login_status = login($_POST["username"], $_POST["pwd"]);
	if ($login_status) {
		// Logged in, now redirect.
		$newDest = 'index.php';
		header('Location: '.$newDest);
	}
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
    Login
  </title>

  <link rel="icon" type="image/x-icon" href="/images/favicon.png">

  <!--Let browser know website is optimized for mobile-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Font(s) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300&display=swap" rel="stylesheet">

  <!-- Main css file-->
  <link href="../css/common.css" rel="stylesheet">
  
	<style>
        blockquote {
            color:redo;
        }
  #loginform {
	max-width: 100%;
	width: 400px;
	font-size:17px;
	margin: 0 auto;
  }
  input,textarea {
	  border: 1px solid gray;
      padding: 10px;
      border-radius: 4px;
      margin: 10px 4px;
      display: block;
	  color: var(--textcolor);
	  box-sizing: border-box;
	  
	  width:100%;
  }
  
  </style>
</head>

<body class="pg-flexbox">

  <div class="pg-flexbox-content">
    <div class="container">
	
      <!-- The Light/Dark mode button-->
      <div style="height: 69px;border-bottom: 1px silver solid;;display: flex;justify-content: space-between;align-items: center;">
        <div>

		</div>
        <label id="lightdark-container">
          <input type="checkbox" id="lightdark-checkbox">
          <div id="lightdark-btn"></div>
        </label>
      </div>
	  

	  <div id="loginform">
	  <h1>Login</h1>
	  <h2></h2>
		<form action="login.php" method="post">
		  <label for="username">Username</label><br>
		  <input type="text" id="username" name="username"><br>
		  <label for="pwd">Password</label><br>
		  <input type="password" id="pwd" name="pwd"><br>
		  <div><?php 
		  if ($login_status === false) {
			echo "Incorrect Username or Password";
		  } else if ($login_status === true) {
			echo "Logged In!";
		  }
		  ?></div>
		  <input type="submit" value="Submit">
		</form>
	  </div>

  </div>
  </div>
  
  <footer class="pg-flexbox-foot">
    <div class="container" id="footnote">
		Don't be doing anything funny here mister 
		<span style="white-space: nowrap;">( ͡≖  ͟ʖ͡≖ )</span>
	</div>
  </footer>
	<!-- For light mode / darkmode -->
    <script src="/js/color-mode.js"></script>
</body>

</html>