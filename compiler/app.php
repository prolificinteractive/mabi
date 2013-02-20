<?php

require_once 'lib/cli.php';
require_once 'lib/lib.php';
require_once 'lib/init.php';


// Read the file specified in the command line args
$json = file( $path );

stdNotice( 'Found ' . count($json) . ' lines. Parsing...' );

$json = implode( PHP_EOL, $json );
$o = json_decode( $json );

stdNotice( 'Generating model for ' . $o->name );
echo PHP_EOL;

#print_r($o);

$iOS = array();
$iOS[] = '//';
$iOS[] = '// Food.h';
$iOS[] = '// FoodTweekModel';
$iOS[] = '//';

foreach ( $o->comments as $comment ) {
    $iOS[] = '// ' . $comment;
}

$iOS[] = '//';
$iOS[] = '';

foreach ( $o->imports as $import ) {
    $iOS[] = '#import <' . $import . '>';
}



stdOut( implode(PHP_EOL, $iOS) );

stdOut( '' );
stdNotice( 'Model generation complete.' );
stdOutCompact( '' );