<?php


define( 'CUSTOM_ERROR_HANDLING', false );

// Don't allow PHP to stop after global timeout setting
set_time_limit( 0 );

// Remove output buffering
while ( ob_get_level() ) ob_end_clean();

// Output buffers directly
ob_implicit_flush( true );

// Start timing execution duration
$time_start = microtime( true );

// Grab the file passed via command line argument
$path = ( empty($argv[1]) ? null : $argv[1] );
#$path = str_replace( '\\r', '\\\\r', $path );

// Make sure the path is correct
$path = (
    file_exists($path) ? $path :
    // Try prepending the current directory
    (getcwd() . DIRECTORY_SEPARATOR . $path)
);

// Make sure path exists again
$path = (
    (file_exists($path) && is_file($path))
    ? $path : null
);