<?php

class ScreenOverview extends Game
{

  // SCREEN: OVERVIEW
  // Show map, land grants
  public function main($data)
  {
    $input = $this->input($data);
    $output = $this->common($data);

    // Return screen name handling the request
    $output['scr'] = 'overview';

    $this->checkPlayer();

    // Initialize
    if(!$this->row['state']) {
      // Generate Map
      if(!isset($this->row['data']['map'])) {
        $mapCols = array('A','B','C','D','E','F','G');
        $mapMeta = array('A','B','C','D','E','F','G');
        $icon = array('M','m','P','P','P','P');
        $type = array('F','E','O','C','','','');
        $map = array();
        foreach ($mapCols as $col) {
          for($i=1; $i<=5; $i++) {
            $plot = $icon[rand(0,5)];
            $plotType = $type[rand(0,6)];
            $map[$col][$i] = $plot;
            $mapMeta[$col][$i] = $plotType . ',' . rand(1,3);
            if($col == 'D') {
              $map[$col][$i] = 'W';
              if($i == 3) {
                $map[$col][$i] = 'S';
              }
            }
          }
        }
        $save['data'] = $this->row['data'];
        $save['data']['map'] = $map;
      }

      $queue['out'] = 'Land Grants';
      $queue['poll'] = '2';
      $this->queue($queue);
      $save['state'] = 'waitforgrants';
      $this->save($save);
    }

    // Land Grants
    if($this->row['state'] == 'waitforgrants') {
      if($input['ack']) {
        // User ack'ed their land
        $plot = strtoupper(substr($input['ack'],0,2));
        if(strlen($plot) == 2 && !in_array($plot,$this->row['data'.$this->player]['land'])) {
          $save['ack' . $this->player] = $plot;
          $save['data'.$this->player] = $this->row['data'.$this->player];
          $save['data'.$this->player]['land'][] = $plot;
          $this->save($save);
          $output['msg'] = 'You got plot ' . $plot . '.';
          $queue['out'] = $this->displayName .' got plot ' . $plot . '.';
          $this->queue($queue);
        } else {
          $output['msg'] = 'Plot ' . $plot . ' is not valid.';
        }
        return $this->output($output);
      }

      // Check how many acks received
      $numPlayers = $this->row['data']['numPlayers'];
      $numAcks = $this->getAcks();
      // All players acknowledged, now hand out random events
      if($numAcks >= $numPlayers) {
        $save['clear'] = 'acks';
        $save['state'] = 'waitforrand';
        $this->save($save);
        $queue['out'] = 'Grants finished.';
        $this->queue($queue);
        return $this->output($output);
      }
    }

    // Land Grants
    if($this->row['state'] == 'waitforrand') {
      // Random event for screen
      $happened = rand(0,2);
      if($happened) {
        if($happened == 1) {
          // Something good happened
          $queue['out'] = "Something good happened.";
          $this->queue($queue);
        } else {
          // Something bad happened
          $queue['out'] = "Something bad happened.";
          $this->queue($queue);
        }
      }

      $save['clear'] = 'acks';
      $save['state'] = 'waitforack';
      $this->save($save);
      $queue['out'] = 'Random events finished.';
      $this->queue($queue);
      return $this->output($output);
    }

    // Final acknowledgement
    if($this->row['state'] == 'waitforack') {

      if($input['ack']) {
        // User ack'ed
        $save['ack' . $this->player] = $input['ack'];
        $this->save($save);
        $output['msg'] = 'You acknowledged.';
        $queue['out'] = $this->displayName .' has acknowledged.';
        $this->queue($queue);
      }

      $numPlayers = $this->row['data']['numPlayers'];
      $numAcks = $this->getAcks();
      // All players acknowledged
      if($numAcks >= $numPlayers) {
        $save['clear'] = '';
        $save['screen'] = 'auction';
        $this->save($save);
        $queue['out'] = 'All users acknowledged. Going to auction screen...';
        $this->queue($queue);
        return $this->output($output);
      }

      $output['state'] = 'waitforack';
      return $this->output($output);
    }

    $output['state'] = 'wait';
    return $this->output($output);
  }

}
?>
