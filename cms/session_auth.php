<?php
// ========================================================
//
//         Authenticate Session Cookies via require
//
// ========================================================
// Return db connection AND session details if session valid.
// Ridirects and returns false if session not valid.
function authenticate_session() {
	if(isset($_COOKIE["session_token"])) {
		$session_cookie = htmlspecialchars($_COOKIE["session_token"]);
		// Check if Cookie matches in sessions table
		$db_path = __DIR__ . '/../../sqlite/data.db'; // __DIR__ always returns /cms regardless where its called from
		$pdo = new PDO('sqlite:'.$db_path);
		$stmt = $pdo->prepare('SELECT * FROM sessions WHERE token = ?');
		$stmt->execute([$session_cookie]);

		$session_match = $stmt->fetch();
		if ($session_match) {
			// Session exists but checks if its expired.
			if($session_match['ttl'] > time()) {
				//return authenticated db connection.
				return [$pdo, $session_match];
			}
		} else {
			// Delete invalid cookie before redirecting to login
			// Reason: To stop browser session check loop from login.php
			unset($_COOKIE['session_token']); 
			setcookie('session_token', null, -1, '/'); 
		}
	}
	
	// Cookie not set or invalid. Redirect to login.
	http_response_code(401);
	$newDest = '/cms/login.php';
	echo "Error: Not authenticated";
	header('Location: '.$newDest);
	return false;
}
[$auth_pdo, $session_details] = authenticate_session();

if ($auth_pdo === null) {die();} // Exit php file if not validated
// =======================================================
?>