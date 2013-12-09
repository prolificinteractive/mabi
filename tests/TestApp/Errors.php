<?php

namespace mabiTesting;

use MABI\ErrorResponseDictionary;

class Errors extends ErrorResponseDictionary {

  public static $TEST_NEW_ERROR = array(
    'TEST_NEW_ERROR' => array(
      'message' => 'New test error with !replacement',
      'httpcode' => 401,
      'code' => 1
    )
  );

  // Used to test overrides on default errors
  public static $NOT_AUTHORIZED = array(
    'NOT_AUTHORIZED' => array(
      'message' => 'Why don\'t you just get out of here, ok?',
      'httpcode' => 401,
      'code' => 1007
    )
  );

  /**
   * @ignore
   */
  public static $IGNORED_STATIC = 'blah';

  public static $INVALID_DEF1 = array(
    'NOT_AUTHORIZED' => array(
      'message' => 'Why don\'t you just get out of here, ok?',
      'code' => 1007
    )
  );

  public static $INVALID_DEF2 = array();

  public static $INVALID_DEF3 = 'blah';
}
