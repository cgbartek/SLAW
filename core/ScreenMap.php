<?php

class ScreenMap extends Game
{

  // SCREEN: MAP
  // Move around map, buy/sell/transfer mules, assay land, hunt, gamble
  public function main($data)
  {
    $input = $this->input($data);
    $output = $this->common($data);

    // Return screen name handling the request
    $output['scr'] = 'map';

    $this->checkPlayer();

    // Initialize
    if(!$this->row['state']) {
      $queue['out'] = 'Map Screen';
      $queue['poll'] = '2';
      $this->queue($queue);
      $save['state'] = 'playerevt';
      $this->save($save);
      $output['state'] = 'playerevt';
      return $this->output($output);
    }

    // Player event
    if($this->row['state'] == 'playerevt') {
      $output['msg'] = 'Your turn.';
      $queue['out'] = $this->displayName . "'s turn.'";
      $this->queue($queue);

      // Random Event
      $happened = rand(0,2);
      if($happened) {
        if($happened == 1) {
          // Something good happened
          $queue['out'] = "Something good happened to ".$this->displayName;
          $this->queue($queue);
        } else {
          // Something bad happened
          $queue['out'] = "Something bad happened to ".$this->displayName;
          $this->queue($queue);
        }
      }
      $save['state'] = 'playerack';
      $this->save($save);
      return $this->output($output);
    }

    // Player ack
    if($this->row['state'] == 'playerack') {

      if($input['ack']) {
        // User ack'ed
        $save['ack' . $this->player] = $input['ack'];
        $save['data'] = $this->row['data'];
        $save['data']['pturn'] = $this->player;
        $save['state'] = 'playerturn';
        $this->save($save);
        $output['msg'] = 'You acknowledged.';
        $queue['out'] = $this->displayName .' acknowledged.';
        $this->queue($queue);
        return $this->output($output);
      }
    }

    // Player turn
    if($this->row['state'] == 'playerturn') {
      $output['tminus'] = $this->getCountdown();

      // Countdown doesn't exist
      if(!$this->row['tstart'][0] || !$this->row['tend'][0]) {
        $output['tminus'] = $this->setCountdown(30);
      }

      // Countdown finished
      if($output['tminus'] <= 0) {
        $queue['out'] = $this->displayName . '\'s turn ended.';
        $this->queue($queue);
        // commented out for testing
        //return $this->output($output);
      }

      // Movement (map)
      if(isset($input['move']) && strlen($input['move']) == 2) {
        $x = $input['move'][0];
        $y = $input['move'][1];
        if(!is_numeric($x) && is_numeric($y)) {
          $save['data'.$this->player] = $this->row['data'.$this->player];
          $save['data'.$this->player]['pos']['map'] = $input['move'];
          $this->save($save);
          $output['msg'] = 'You set your position to ' . $input['move'];
        } else {
          $output['msg'] = 'Invalid map coordinates.';
        }

        return $this->output($output);
      }
      // Movement (xy)
      if(isset($input['move']) && strlen($input['move']) != 2) {
        $xy = explode(',',$input['move']);
        $x = $xy[0];
        $y = $xy[1];
        if(is_numeric($x) && is_numeric($y)) {
          $save['data'.$this->player] = $this->row['data'.$this->player];
          $save['data'.$this->player]['pos']['xy'] = $x.','.$y;
          $this->save($save);
          $output['msg'] = 'You set your position to ' . $x.','.$y;
        } else {
          $output['msg'] = 'Invalid xy coordinates.';
        }

        return $this->output($output);
      }

      // Acquire item
      if(isset($input['pick']) && strlen($input['pick']) == 1) {
        $prices = array("M" => "100", "A" => "50");
        $items = array("M" => "MULE", "A" => "Land Assay");
        // Bought a MULE
        if($input['pick'] == 'M') {
          if($this->row['data'.$this->player]['item'] != "M") {
            $save['data'.$this->player] = $this->row['data'.$this->player];
            if($this->row['data'.$this->player]['stats']['money'] < $prices[$input['pick']]) {
              $output['msg'] = 'You can\'t afford ' . $items[$input['pick']] . '!';
              return $this->output($output);
            } else {
              $save['data'.$this->player]['stats']['money'] = $this->row['data'.$this->player]['stats']['money'] - $prices[$input['pick']];
              $output['msg'] = 'You bought ' . $items[$input['pick']] . ' for ' . $prices[$input['pick']] .'.';
            }

            $save['data'.$this->player]['item'] = $input['pick'];

            $queue['out'] = $this->displayName .' bought a MULE.';
            $this->queue($queue);
            $this->save($save);
          } else {
            // Already has MULE
            $output['msg'] = 'You already have a MULE!';
            return $this->output($output);
          }
        }
      }

      // Bought subitem
      if(isset($input['pick']) && ($input['pick'] == 'F' || $input['pick'] == 'E' || $input['pick'] == 'O' || $input['pick'] == 'C')) {
        // Make sure player has a MULE first
        if($this->row['data'.$this->player]['item'] == 'M') {
          $subitems = array("F" => "food", "E" => "energy", "O" => "ore", "C" => "crystal");
          $save['data'.$this->player] = $this->row['data'.$this->player];
          $save['data'.$this->player]['subitem'] = $input['pick'];
          $output['msg'] = 'You bought ' . $subitems[$input['pick']];
          $queue['out'] = $this->displayName .' bought a MULE.';
          $this->queue($queue);
          $this->save($save);
          return $this->output($output);
        } else {
          $output['msg'] = 'You need a MULE first!';
          return $this->output($output);
        }
      }

      // Place item on land
      if(isset($input['pick']) && strlen($input['pick']) == 2) {
        $plot = strtoupper($input['pick']);
        $item = $this->row['data'.$this->player]['item'];
        $subitem = $this->row['data'.$this->player]['subitem'];
        // Make sure player has an item
        if($item && $subitem) {
          $subitems = array("F" => "food", "E" => "energy", "O" => "ore", "C" => "crystal");
          //$save['data'.$this->player] = $this->row['data'.$this->player];
          //$save['data'.$this->player]['subitem'] = $input['pick'];
          $output['msg'] = 'You placed '.$item.' with '.$subitem.' on '.$plot.'.';
          //$queue['out'] = $this->displayName .' bought a MULE.';
          //$this->queue($queue);
          //$this->save($save);
          return $this->output($output);
        } else {
          $output['msg'] = 'You have no item to place on plot!';
          return $this->output($output);
        }
      }
    }

    return $this->output($output);
  }

}
?>
