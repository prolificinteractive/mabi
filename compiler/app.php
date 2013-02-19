<?php

require_once 'lib/cli.php';
require_once 'lib/lib.php';
require_once 'lib/init.php';


// Read the file specified in the command line args
$data = file( $path );

stdNotice( 'Found ', count($data) . ' lines. Parsing...' );

$data = implode( PHP_EOL, $data );
$o = json_decode( $data );

stdNotice(sprintf( 'Generating %s classe(s) for model %s.', count($o->classes), $o->name ));

foreach ( $o->classes as $class ) {
    
    
    
}


stdNotice( 'Model generation complete.' );