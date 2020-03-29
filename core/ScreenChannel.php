<?php
include_once ('Game.php');
class ScreenChannel extends Game
{

  // SCREEN: CHANNEL
  // Returns list of players, countdown, game mode
  public function main($data)
  {
    $input = $this->input($data);
    $output = $this->common($input);

    // Return screen name handling the request
    $output['scr'] = 'channel';

    $output['tminus'] = $this->getCountdown();

    // Countdown doesn't exist
    if(!$this->row['tstart'][0] || !$this->row['tend'][0]) {
      $output['tminus'] = $this->setCountdown(30);
      $output['state'] = 'init';
      $queue['out'] = 'Initialized.';
      $queue['poll'] = '3';
      $this->queue($queue);
      return $this->output($output);
    }

    // Countdown finished
    if($output['tminus'] <= 0) {
      // How many players joined?
      $numPlayers = 0;
      for ($i=1; $i<=4; $i++) {
        if($this->row['ack'.$i]) {
          $numPlayers++;
        }
      }
      // If at least one player joined, move to next screen
      if($numPlayers) {
        $save['clear'] = '';
        $save['screen'] = 'start';
        $save['data']['numPlayers'] = $numPlayers;
        $this->save($save);

        $queue['out'] = 'Countdown finished. Going to start screen...';
        $this->queue($queue);
      } else {
        // Not enough players joined, stay on this screen
        $this->setCountdown(30);
        $output['state'] = 'init';
        $queue['out'] = 'Still waiting for players.';
        $this->queue($queue);
      }

      return $this->output($output);
    }

    $output['state'] = 'wait';
    return $this->output($output);
  }

}
?>
