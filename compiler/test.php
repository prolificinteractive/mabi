<?php

header( 'Content-type: text/plain' );

$data = file( 'examples/foodtweeks.js' );

echo PHP_EOL;
echo 'Found ', count($data), ' lines. Parsing...';

$data = implode( PHP_EOL, $data );
$data = json_decode( $data );

echo PHP_EOL;
echo print_r( $data );

echo PHP_EOL;
echo 'Finished.';
echo PHP_EOL;
