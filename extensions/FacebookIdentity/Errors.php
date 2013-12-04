<?php

namespace MABI\FacebookIdentity;

include_once __DIR__ . '/../../ErrorResponseDictionary.php';

use MABI\ErrorResponseDictionary;

class Errors extends ErrorResponseDictionary {
  public static $TOKEN_REQUIRED = array(
    'FB_IDENTITY_TOKEN_REQUIRED' => array(
      'message' => 'An authorization token is required to create a session',
      'httpcode' => 400,
      'code' => 1000
    )
  );

  public static $FB_ONLY = array(
    'FB_IDENTITY_ONLY' => array(
      'message' => 'Facebook Connect is the only method allowed to create users.',
      'httpcode' => 401,
      'code' => 1001
    )
  );

}