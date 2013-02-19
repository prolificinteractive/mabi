<?php

header( 'Content-type: text/plain' );

echo PHP_EOL;

// Custom error handling
if ( @CUSTOM_ERROR_HANDLING ) {
    error_reporting( 0 );
    function handleError( $errno, $errmsg, $filename, $linenum, $vars ) {
        stdError(
            '[' . $errno . '] Line #' . $linenum .
            PHP_EOL . $errmsg . PHP_EOL . ' in ' . $filename
        );
    }
    $old_error_handler = set_error_handler( 'handleError' );
}

// Only allow this script to be run via the command line
if ( strtoupper(PHP_SAPI) !== 'CLI' ) {
    stdError( 'This script can only be run via the command line.', false );
    showUsage();
    exit( 1 );
}

// Make sure we have a file to work with
if ( defined('REQUIRES_ARG') && !!REQUIRES_ARG ) {
    if ( count($argv) <= 1 ) {
        stdError( 'You must provide a JSON file URI to parse.', false );
        showUsage();
        exit( 1 );
    }
}

// Make sure path is valid
if ( !file_exists($path) ) {
    stdError( 'Invalid file path.' );
}