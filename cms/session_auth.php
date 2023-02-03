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
		$pdo = new PDO('sqlite:../../data.db');
		$stmt = $pdo->prepare('SELECT * FROM sessions WHERE token = ?');
		$stmt->execute([$session_cookie]);

		$session_match = $stmt->fetch();
		if ($session_match) {
			// Session exists but checks if its expired.
			if($session_match['ttl'] > time()) {
				//return authenticated db connection.
				return [$pdo, $session_match];
			}
		}
	}
	
	// Cookie not set or invalid. Redirect to login.
	http_response_code(401);
	$newDest = 'login.php';
	echo "Error: Not authenticated";
	header('Location: '.$newDest);
	return false;
}
[$auth_pdo, $session_details] = authenticate_session();

if ($auth_pdo === null) {die();} // Exit php file if not validated
// =======================================================
?>