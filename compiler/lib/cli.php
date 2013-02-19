<?php

// Wrapper for more spacious console output
function stdOut( $message ) {
    echo PHP_EOL, $message, PHP_EOL, PHP_EOL;
}

function stdOutCompact( $message ) {
    echo PHP_EOL, $message;
}

function stdError( $message, $fail = true ) {
    stdOut( 'Error: ' . $message );
    if ( $fail ) exit( 1 );
}

function stdNotice( $message ) {
    echo PHP_EOL, '** ', $message/*, PHP_EOL*/;
}

function showUsage() {
    stdNotice( 'USAGE: **' );
    stdOut( 'php -f ' . $argv[0] . ' <json_input_path>' );
}



// David Walsh
class timer {
  var $start;
  var $pause_time;

  /*  start the timer  */
  function timer($start = 0) {
    if($start) { $this->start(); }
  }

  /*  start the timer  */
  function start() {
    $this->start = $this->get_time();
    $this->pause_time = 0;
  }

  /*  pause the timer  */
  function pause() {
    $this->pause_time = $this->get_time();
  }

  /*  unpause the timer  */
  function unpause() {
    $this->start += ($this->get_time() - $this->pause_time);
    $this->pause_time = 0;
  }

  /*  get the current timer value  */
  function get($decimals = 8) {
    return round(($this->get_time() - $this->start),$decimals);
  }

  /*  format the time in seconds  */
  function get_time() {
    list($usec,$sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
  }
}
