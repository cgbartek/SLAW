<?php
// Users

/**
 * Name: userAdd
 * Description: Adds a user to the database.
 * Example: /api/?action=userAdd&username=&password=&passwordVerify
 *
 * @param array $input username, password, passwordVerify
 * @return array Returns success or error.
 */
function api_userAdd($input) {
	$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
	if($db->connect_errno){
		die('Database error [' . $db->connect_error . ']');
	}

	$username = mysqli_real_escape_string($db, $input['username']);
	$password = $input['password'];
	$passwordVerify = $input['passwordVerify'];
	$passHash = password_hash($password, PASSWORD_BCRYPT);

	if(strlen($username) < 3) {
		$return['error'] = "Username must be larger than 3 characters.";
		return json_encode($return);
	}
	if(strlen($password) < 6) {
		$return['error'] = "Password must be larger than 6 characters.";
		return json_encode($return);
	}
	if($password != $passwordVerify) {
		$return['error'] = "Passwords do not match.";
		return json_encode($return);
	}

	$sql = "SELECT username FROM users WHERE username = '$username'";
	if($result = $db->query($sql)){
		$row = $result->fetch_assoc();
		if(isset($row['username'])) {
			$return['error'] = "Username already exists.";
			return json_encode($return);
		}
	}

	if($_SESSION['role'] ?? '' != 'A') {
		$return['error'] = "Not authorized.";
		return json_encode($return);
	}

	$sql = "INSERT INTO users (username, password) VALUES ('$username', '".$passHash."')";
	if($result = $db->query($sql)){
		$return['success'] = "Account created successfully.";
		return json_encode($return);
	} else {
		die('Query error [' . $db->error . ']');
	}

	return json_encode($return);
}


/**
 * Name: userLogin
 * Description: Log in a user.
 * Example: /api/?action=userLogin&username=&password=
 *
 * @param array $input username, password, passwordVerify
 * @return array Returns success or error.
 */
function api_userLogin($input) {
	$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
	if($db->connect_errno){
		die('Database error [' . $db->connect_error . ']');
	}

	$username = mysqli_real_escape_string($db, $input['username']);
	$password = $input['password'];
	//$passHash = password_hash($password, PASSWORD_BCRYPT);

	if(strlen($username) < 3) {
		$return['error'] = "Username must be larger than 3 characters.";
		return (json_encode($return));
	}
	if(strlen($password) < 6) {
		$return['error'] = "Password must be larger than 6 characters.";
		return (json_encode($return));
	}

	$sql = "SELECT * FROM users WHERE username = '$username'";
	if($result = $db->query($sql)){
		$row = $result->fetch_assoc();
		if($row['password']) {
			if (password_verify($password, $row['password'])) {
				$_SESSION['id'] = $row['id'];
				$_SESSION['username'] = $row['username'];
				$_SESSION['displayname'] = $row['displayname'];
				$_SESSION['email'] = $row['email'];
				$_SESSION['role'] = $row['role'];
				$return['success'] = "Logged in successfully.";
				return (json_encode($return));
			} else {
				$return['error'] = "Incorrect username or password.";
				return (json_encode($return));
			}
		}
	} else {
		die('Query error [' . $db->error . ']');
	}

	return (json_encode($return));
}

?>
