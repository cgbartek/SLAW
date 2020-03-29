<?php
// Games

/**
 * Name: gameMain
 * Description: Runs the main game.
 * Example: /api/?action=gameMain&data={"ack":1}
 *
 * @param string $input data
 * @return array Returns success or error.
 */
function api_gameMain($input) {
	$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
	if($db->connect_errno){
		die('Database error [' . $db->connect_error . ']');
	}
	$sql = "SELECT screen FROM games WHERE id = '$_SESSION[game_id]'";
	if($result = $db->query($sql)){
		$row = $result->fetch_assoc();
		// Route the game to the appropriate screen
		if($row['screen'] == 'channel')
			$Game = new ScreenChannel;
		if($row['screen'] == 'start')
			$Game = new ScreenStart;
		if($row['screen'] == 'score')
			$Game = new ScreenScore;
		if($row['screen'] == 'overview')
			$Game = new ScreenOverview;
		if($row['screen'] == 'auction')
			$Game = new ScreenAuction;
		if($row['screen'] == 'map')
			$Game = new ScreenMap;

		return $Game->main($input);
	}
}


/**
 * Name: gameAdd
 * Description: Adds a game to the database.
 * Example: /api/?action=gameAdd&game=
 *
 * @param array $input game
 * @return array Returns success or error.
 */
function api_gameAdd($input) {
	$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
	if($db->connect_errno){
		die('Database error [' . $db->connect_error . ']');
	}

	$game = mysqli_real_escape_string($db, $input['game']);
	$game = trim(str_replace(' ', '-', $game));
  $game = preg_replace('/[^A-Za-z0-9\-]/', '', $game);

	if(strlen($game) < 3) {
		$return['error'] = "Name must be larger than 3 characters.";
		return json_encode($return);
	}

	$sql = "SELECT name FROM games WHERE name = '$game'";
	if($result = $db->query($sql)){
		$row = $result->fetch_assoc();
		if(isset($row['name'])) {
			$return['error'] = "Game with this name already exists.";
			return json_encode($return);
		}
	}

	if(!$_SESSION['id']) {
		$return['error'] = "Not authorized.";
		return json_encode($return);
	}

	$sql = "INSERT INTO games (name) VALUES ('$game')";
	if($result = $db->query($sql)){
		$return['success'] = "Game \"$game\" created successfully.";
		return json_encode($return);
	} else {
		die('Query error [' . $db->error . ']');
	}

	return json_encode($return);
}


/**
 * Name: gameList
 * Description: Lists all games in the database.
 * Example: /api/?action=gameList
 *
 * @return array Returns success or error.
 */
function api_gameList($input) {
	$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
	if($db->connect_errno){
		die('Database error [' . $db->connect_error . ']');
	}

	$sql = "SELECT name FROM games";
	if($result = $db->query($sql)){
		while ($row = $result->fetch_assoc()) {
			$return['success'] .= $row['name'] . ',';
		}
		$return['success'] = substr($return['success'], 0, -1);
		return json_encode($return);
	} else {
		die('Query error [' . $db->error . ']');
	}

}


/**
 * Name: gameJoin
 * Description: Joins a game.
 * Example: /api/?action=gameJoin&game=
 *
 * @param string $input game
 * @return array Returns success or error.
 */
function api_gameJoin($input) {
	$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
	if($db->connect_errno){
		die('Database error [' . $db->connect_error . ']');
	}

	$game = mysqli_real_escape_string($db, $input['game']);
	$game = trim(str_replace(' ', '-', $game));
  $game = preg_replace('/[^A-Za-z0-9\-]/', '', $game);

	if(strlen($game) < 3) {
		$return['error'] = "Name must be larger than 3 characters.";
		return json_encode($return);
	}

	if(!$_SESSION['id']) {
		$return['error'] = "Not authorized.";
		return json_encode($return);
	}

	$sql = "SELECT * FROM games WHERE name = '$game'";
	if($result = $db->query($sql)){
		$row = $result->fetch_assoc();
		if(isset($row['name'])) {
			// Find an empty ack slot
			for($i=1; $i<=4; $i++) {
				if($row['ack'.$i] == $_SESSION['id']) {
					$return['error'] = "Already joined game.";
					return json_encode($return);
				}
				if(!$row['ack'.$i] && $row['ack'.$i] != $_SESSION['id']) {
					$sql = 'UPDATE games SET ack'.$i.'="'.$_SESSION['id'].'" WHERE id="'.$_SESSION['game_id'].'"';
					mysqli_query($db, $sql);
					$sql = 'UPDATE games SET screen="channel" WHERE id="'.$_SESSION['game_id'].'"';
					mysqli_query($db, $sql);
					$_SESSION['player'] = $i;
					break;
				}
				if($i == 4) {
					$return['error'] = "Players maxed out, can't join game.";
					return json_encode($return);
				}
			}
			$_SESSION['game_id'] = $row['id'];
			$return['success'] = "Joined Game \"" . $row['name'] . "\".";

			return json_encode($return);
		} else {
			$return['error'] = "Game \"$game\" not found.";
		}
	}
}


/**
 * Name: gameRestart
 * Description: Restarts a game.
 * Example: /api/?action=gameRestart&game=
 *
 * @param string $input game
 * @return array Returns success or error.
 */
function api_gameRestart($input) {
	$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
	if($db->connect_errno){
		die('Database error [' . $db->connect_error . ']');
	}

	if(!$input['game']) {
		$input['game'] = $_SESSION['game_id'];
	}

	if($input['game']) {
		$sql = 'UPDATE games SET screen="", state="", tstart=NULL, tend=NULL, ack1="", ack2="", ack3="", ack4="", data="", data1="", data2="", data3="", data4="" WHERE id="'.$input['game'].'"';
		mysqli_query($db, $sql);
		$sql = 'DELETE FROM queue WHERE game_id="'.$input['game'].'"';
		mysqli_query($db, $sql);
	}

	$return['success'] = "Restarted Game \"" . $input['game'] . "\".";
	return json_encode($return);
}
?>
