<?php

namespace MABI;

include_once __DIR__ . '/ErrorResponseDictionary.php';

class DefaultAppErrors extends ErrorResponseDictionary {
  public static $NOT_AUTHORIZED = array(
    'NOT_AUTHORIZED' => array(
      'message' => 'Not properly authenticated for this route',
      'httpcode' => 401,
      'code' => 1007
    )
  );

  public static $ENTRY_EXISTS = array(
    'ENTRY_EXISTS' => array(
      'message' => 'An entry with the id !modelid already exists.',
      'httpcode' => 409,
      'code' => 1008
    )
  );

  public static $INVALID_JSON = array(
    'INVALID_JSON' => array(
      'message' => 'Could not load model from json: !message',
      'httpcode' => 400,
      'code' => 1009
    )
  );
}