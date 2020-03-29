<?php

class ScreenStart extends Game
{

  // SCREEN: START
  // Start of game, intro, player select
  public function main($data)
  {
    $input = $this->input($data);
    $output = $this->common($data);

    $this->checkPlayer();

    // Return screen name handling the request
    $output['scr'] = 'start';

    $output['tminus'] = $this->getCountdown();

    // Countdown doesn't exist
    if(!$this->row['tstart'][0] || !$this->row['tend'][0]) {
      $this->setCountdown(30);

      $queue['out'] = 'Welcome to S*L*A*W.';
      $queue['poll'] = '4';
      $this->queue($queue);

      $output['state'] = 'init';
      return $this->output($output);
    }

    // Countdown finished
    if($output['tminus'] <= 0) {
      // Check how many acks received
      $numPlayers = $this->row['data']['numPlayers'];
      $numAcks = $this->getAcks();
      // All players acknowledged, go to score screen
      if($numAcks >= $numPlayers) {
        $save['clear'] = '';
        $save['screen'] = 'score';
        $this->save($save);

        $queue['out'] = 'Countdown finished. Going to score screen...';
        $this->queue($queue);

        return $this->output($output);
      } else {
        // Not all players acknowledged, stay on this screen
        $this->setCountdown(30);
        $queue['out'] = 'Still waiting for players.';
        $this->queue($queue);

        return $this->output($output);
      }
    }

    //if user picks character and color
    if(isset($input['ack'])) {
      // Add player as acklowledged and save
      $save['ack'.$this->player] = $input['ack'];
      $save['data'.$this->player] = $this->row['data'.$this->player];
      $save['data'.$this->player]['type'] = $input['ack'];
      $this->save($save);

      $output['msg'] = 'You submitted your character type.';

      $queue['out'] = $this->displayName .' has chosen a character.';
      $this->queue($queue);

      return $this->output($output);
    }

    $output['state'] = 'wait';
    return $this->output($output);
  }

}
?>
