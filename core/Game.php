<?php

/*
  Main game object. This includes all variables and helper functions needed to
  load game screens.
*/

class Game
{

  public $id;
  public $gameId;
  public $player;
  public $displayName;
  public $row;

  public function __construct()
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }
    $this->id = $_SESSION['id'] ?? 0;
    $this->gameId = $_SESSION['game_id'] ?? 0;
    $this->player = $_SESSION['player'] ?? 0;
    $this->username = $_SESSION['username'] ?? '';
    $this->email = $_SESSION['email'] ?? '';
    $this->role = $_SESSION['role'] ?? '';
    $this->displayName = $_SESSION['displayname'] ?? 'Player ' . $this->player;

    // Create connection
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    if ($db->connect_error) {
      $output['error'] = "DB connection failed. " . $db->connect_error;
      $this->output($output);
    } else {
      $sql = "SELECT * FROM games WHERE id=" . $this->gameId;
      $result = $db->query($sql);
      if ($result->num_rows > 0) {
        $this->row = $result->fetch_assoc();

        // convert player json to array
        // this easily and automatically lets us deal with the data natively
        for ($i=1; $i <= 4; $i++) {
          if($this->row['data'.$i]) {
            $this->row['data'.$i] = json_decode($this->row['data'.$i],1);
          } else {
            $this->row['data'.$i] = array();
          }
        }
        if($this->row['data']) {
          $this->row['data'] = json_decode($this->row['data'],1);
        } else {
          $this->row['data'] = array();
        }

      } else {
        $output['error'] = "Game not found.";
        $this->output($output);
      }
    }
  }


  // Data operations common to all screens
  protected function common($input)
  {
    $output = array();
    // User sent a message
    if(isset($input['say'])) {
      $queue['out'] = $this->displayName . ': ' . $input['say'];
      $this->queue($queue);
    }

    // Get from queue
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    //$sql = "SELECT * FROM queue WHERE game_id=" . $this->gameId . " AND ack NOT LIKE '%|". $this->player ."|%'";
    $sql = "SELECT * FROM queue WHERE game_id=" . $this->gameId . " AND NOT FIND_IN_SET('$this->id', ack)";
    $result = $db->query($sql);
    while ($row = $result->fetch_assoc()) {
      if($row['k'] == 'out') {
        $output[$row['k']][] = $row['v'];
      } else {
        $output[$row['k']] = $row['v'];
      }
      $newAck = $this->id;
      if($newAck) {$row['ack'] . ',' . $newAck;}
      $sql = "UPDATE queue SET ack='".$newAck."' WHERE id=" . $row['id'] . ";";
      $db->query($sql);
    }

    return $output;
  }

  // Save (update) the game table
  protected function save($save) {
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    if(isset($save['clear'])) {
      if($save['clear'] == 'acks') {
        $save['ack1'] = '';
        $save['ack2'] = '';
        $save['ack3'] = '';
        $save['ack4'] = '';
      } else {
        if(!isset($save['tstart'])) {$save['tstart'] = '';}
        if(!isset($save['tend'])) {$save['tend'] = '';}
        if(!isset($save['state'])) {$save['state'] = '';}
        if(!isset($save['ack1'])) {$save['ack1'] = '';}
        if(!isset($save['ack2'])) {$save['ack2'] = '';}
        if(!isset($save['ack3'])) {$save['ack3'] = '';}
        if(!isset($save['ack4'])) {$save['ack4'] = '';}
      }
      unset($save['clear']);
    }

    // re-encode all player data
    for ($i=1; $i <= 4; $i++) {
      if(isset($save['data'.$i])) {
        $save['data'.$i] = json_encode($save['data'.$i]);
      }
    }
    if(isset($save['data'])) {
      $save['data'] = json_encode($save['data']);
    }

    $updateSet = 'SET ';
    foreach($save as $k => $v) {
      $k = mysqli_real_escape_string($db, $k);
      $v = mysqli_real_escape_string($db, $v);
      $updateSet .= $k.'="'. $v.'", ';
    }
    $updateSet = substr($updateSet, 0, -2);
    $sql = "UPDATE games $updateSet WHERE id=" . $this->gameId . ";";
    //echo $sql;
    if(!$result = $db->query($sql)) {
      $output['error'] = "Update error.";
      return $this->output($output);
    }
  }

  // Add item to the stack (optionally populate ack to prevent echo)
  protected function queue($queue,$ack='') {
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    foreach($queue as $k => $v) {
      $k = mysqli_real_escape_string($db, $k);
      $v = mysqli_real_escape_string($db, $v);

      $game_id = $this->gameId;
      $created = date("Y-m-d H:i:s",strtotime('now'));
      $expires = date("Y-m-d H:i:s",strtotime('now') + (60 * 60));
      $creator = $this->id;
      $sql = "INSERT INTO queue (game_id, k, v, ack, created, expires, creator)
      VALUES ('$game_id', '$k', '$v', '$ack', '$created', '$expires', '$creator');";
      if(!$result = $db->query($sql)) {
        echo mysqli_error($db);
        $output['error'] = "Insert error.";
        return $this->output($output);
      }
    }
  }

  // Set up a game countdown
  protected function setCountdown($secs) {
    $save['tstart'] = date("Y-m-d H:i:s",strtotime('now'));
    $save['tend'] = date("Y-m-d H:i:s",strtotime('now') + $secs);
    $this->save($save);
    //$output['tminus'] = $secs;
    return $secs;
  }

  // Get current countdown
  protected function getCountdown() {
    return strtotime($this->row['tend']) - strtotime('now');
  }

  // Get number of player acknowledgements
  protected function getAcks() {
    $numAcks = 0;
    for ($i=1; $i<=4; $i++) {
      if($this->row['ack'.$i]) {
        $numAcks++;
      }
    }
    return $numAcks;
  }

  // Verify player is a member of this game
  protected function checkPlayer() {
    if(!$this->player) {
      $output['msg'] = 'Error: You are not a registered player.';
      return $this->output($output);
    }
  }

  // Input processing
  protected function input($input) {
    if(isset($input[0]) && $input[0] == '{') {
      $input = json_decode($input,1);
      if(isset($input['data'])) {
        $input = $input['data'];
      }
      return $input;
    }
    // If data is included as a variable, set that as root
    if(isset($input['data'])) {
      $input = json_decode($input['data'],1);
    }
    // Passthrough as-is if not JSON
    return $input;
  }

  // Output processing
  protected function output($output) {
    return json_encode($output);
  }

}
?>
