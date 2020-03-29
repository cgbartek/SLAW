<?php

class ScreenScore extends Game
{

  // SCREEN: SCORE
  // Display current scores, global messages
  public function main($data)
  {
    $input = $this->input($data);
    $output = $this->common($data);

    // Return screen name handling the request
    $output['scr'] = 'score';

    $this->checkPlayer();

    // Output (and initialize if necessary) Stats
    if($this->row['state'] != 'waitforack') {
      $queue['out'] = 'Top Scores.';
      $queue['poll'] = '4';
      $this->queue($queue);

      for($i=1; $i<=4; $i++) {
        $stats = array();
        if(isset($this->row['data'.$i]['type'])) {
          if(!isset($this->row['data'.$i]['stats'])) {
            $stats['land'] = 0;
            $stats['food'] = 0;
            $stats['energy'] = 0;
            $stats['ore'] = 0;
            $stats['crystal'] = 0;
            $stats['money'] = 500;
            $save['data'.$i] = $this->row['data'.$i];
            $save['data'.$i]['stats'] = $stats;
            $this->save($save);
          } else {
            $stats = $this->row['data'.$i]['stats'];
          }
          $output['p'.$i.'stats'] = $stats;
        }
        $save['state'] = 'waitforack';
        $this->save($save);
      }
    }

    // User ack
    if(isset($input['ack'])) {
      $save['ack' . $this->player] = $input['ack'];
      $this->save($save);

      $output['msg'] = 'You acknowledged.';

      $queue['out'] = $this->displayName .' has acknowledged.';
      $this->queue($queue);

      //return $this->output($output);
    }

    // Check how many acks received
    $numPlayers = $this->row['data']['numPlayers'];
    $numAcks = $this->getAcks();
    // All players acknowledged, go to score screen
    if($numAcks >= $numPlayers) {
      $save['clear'] = '';
      $save['screen'] = 'overview';
      $this->save($save);

      $queue['out'] = 'All users acknowledged. Going to overview screen...';
      $this->queue($queue);

      return $this->output($output);
    }

    $output['state'] = 'wait';
    return $this->output($output);
  }

}
?>
