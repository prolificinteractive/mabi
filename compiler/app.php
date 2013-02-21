<?php

require_once 'lib/cli.php';
require_once 'lib/lib.php';
require_once 'lib/init.php';


// Read the file specified in the command line args
$json = file( $path );

// Show loading
stdNotice( 'Found ' . count($json) . ' lines. Parsing...' );

// Read & parse JSON file
$json = implode( PHP_EOL, $json );
$o = json_decode( $json );

// Show working
stdNotice( 'Generating model for ' . $o->name );
echo PHP_EOL;


// List of lines to be output for iOS model
$iOS = array();
$iOS[] = '//';
$iOS[] = '// Food.h';
$iOS[] = '// FoodTweekModel';
$iOS[] = '//';

// Append any comments
foreach ( $o->comments as $comment ) {
    $iOS[] = '// ' . $comment;
}

// Spacing
$iOS[] = '//';
$iOS[] = '';

// Append #import directives
foreach ( $o->imports as $import ) {
    $iOS[] = '#import <' . $import . '>';
}

// Append properties
foreach ( $o->properties as $prop ) {
    
    /**
     * Property Types:
     * 
     * String       0
     * Array        1
     * Number       2
     * Float        3
     * Boolean      4
     */
    switch ( $prop->type ) {
        
        // String
        case 0:
            $iOS[] = '@property (nonatomic, copy) NSString *' . $prop->name . ';';
            break;
        
        // Array
        case 1:
            $iOS[] = '@property (nonatomic, strong) NSArray *' . $prop->name . ';';
            break;
        
        // @todo: define the rest of these
        case 2:
        case 3:
        case 4:
        default:
            $iOS[] = '@property (nonatomic, copy) NSString *' . $prop->name . ';';
            break;
        
    }
    
}

// Spacing & end
$iOS[] = '';
$iOS[] = '';
$iOS[] = '@end';


// Show or save the iOS model code
stdOut( implode(PHP_EOL, $iOS) );

// Show done
stdOut( '' );
stdNotice( 'Model generation complete.' );
stdOutCompact( '' );