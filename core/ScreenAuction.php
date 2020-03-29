<?php

class ScreenAuction extends Game
{

  // SCREEN: AUCTION
  // Buy and sell resources
  public function main($data)
  {
    $input = $this->input($data);
    $output = $this->common($data);

    // Return screen name handling the request
    $output['scr'] = 'auction';

    $this->checkPlayer();

    // Initialize
    if(!$this->row['state']) {
      $queue['out'] = 'Food Auction. Choose Buy or Sell.';
      $queue['poll'] = '2';
      $this->queue($queue);
      $save['state'] = 'waitfoodack';
      $this->save($save);
      $output['state'] = 'waitfoodack';
      return $this->output($output);
    }

    // Buy or Sell ack
    if($this->row['state'] == 'waitfoodack') {

      $output['tminus'] = $this->getCountdown();

      // Countdown doesn't exist
      if(!$this->row['tstart'][0] || !$this->row['tend'][0]) {
        $output['tminus'] = $this->setCountdown(30);
      }

      if($input['ack']) {
        // User ack'ed
        $mode = strtoupper($input['ack'][0]);
        $modeFull = 'Buy';
        if($mode == 'S') {
          $modeFull = 'Sell';
        }
        $save['ack' . $this->player] = $input['ack'];
        $save['data'.$this->player] = $this->row['data'.$this->player];
        $save['data'.$this->player]['auction'] = $mode;
        $this->save($save);
        $output['msg'] = 'You are a ' . $modeFull . 'er.';
        $queue['out'] = $this->displayName .' is a ' . $modeFull . 'er.';
        $this->queue($queue);
      }

      // Countdown finished
      if($output['tminus'] <= 0) {
        $save['clear'] = '';
        $save['state'] = 'waitfoodbids';
        $this->save($save);
        $queue['out'] = 'Starting food auction...';
        $this->queue($queue);
        return $this->output($output);
      }

      $output['state'] = 'waitfoodack';
      return $this->output($output);

    }

    // Bidding
    if($this->row['state'] == 'waitfoodbids') {

      $output['tminus'] = $this->getCountdown();

      // Countdown doesn't exist
      if(!$this->row['tstart'][0] || !$this->row['tend'][0]) {
        $output['tminus'] = $this->setCountdown(30);
      }

      if($input['ack']) {
        // User sent price/pos
        $save['ack' . $this->player] = $input['ack'];
        $save['data'.$this->player] = $this->row['data'.$this->player];
        $save['data'.$this->player]['price'] = $input['ack'];
        $this->save($save);
        $output['msg'] = 'Set price to '.$input['ack'].'.';
        $queue['out'] = $this->displayName .' set price to ' . $input['ack'] . '.';
        $this->queue($queue);
      }

      // Countdown finished
      if($output['tminus'] <= 0) {
        $save['clear'] = '';
        $save['screen'] = 'map';
        $this->save($save);
        $queue['out'] = 'Auctions ended. Going to map...';
        $this->queue($queue);
        return $this->output($output);
      }

      $output['state'] = 'waitfoodbids';
      return $this->output($output);

    }

    return $this->output($output);

  }

}
?>
