<?php

namespace MABI\Identity;

include_once __DIR__ . '/../../ErrorResponseDictionary.php';

use MABI\ErrorResponseDictionary;

class Errors extends ErrorResponseDictionary {
  public static $SESSION_EMAIL_REQUIRED = array(
    'IDENTITY_SESSION_EMAIL_REQUIRED' => array(
      'message' => 'Email is required to create a session',
      'httpcode' => 400,
      'code' => 1002
    )
  );

  public static $PASSWORD_INVALID = array(
    'IDENTITY_PASSWORD_INVALID' => array(
      'message' => 'Password is invalid',
      'httpcode' => 400,
      'code' => 1003
    )
  );

  public static $TOKEN_INVALID = array(
    'IDENTITY_TOKEN_INVALID' => array(
      'message' => 'AuthToken is invalid',
      'httpcode' => 400,
      'code' => 1003
    )
  );

  public static $SESSION_PASSWORD_TOKEN_REQUIRED = array(
    'IDENTITY_SESSION_PASSWORD_TOKEN_REQUIRED' => array(
      'message' => 'A Password or an authToken are required to create a session',
      'httpcode' => 400,
      'code' => 1002
    )
  );

  public static $SHORT_PASSWORD = array(
    'IDENTITY_SHORT_PASSWORD' => array(
      'message' => 'Password must be at least 6 characters',
      'httpcode' => 400,
      'code' => 1004
    )
  );

  public static $EMAIL_REQUIRED = array(
    'IDENTITY_EMAIL_REQUIRED' => array(
      'message' => 'Email is required',
      'httpcode' => 400,
      'code' => 1005
    )
  );

  public static $EMAIL_EXISTS = array(
    'IDENTITY_EMAIL_EXISTS' => array(
      'message' => 'An account with this email already exists',
      'httpcode' => 409,
      'code' => 1006
    )
  );

  public static $PASSWORD_NO_USER_EMAIL = array(
    'FORGET_PASSWORD_NO_USER_EMAIL' => array(
      'message' => 'There is no user associated with this email',
      'httpcode' => 400,
      'code' => 1011
    )
  );

  public static $PASSWORD_EMAIL_REQUIRED = array(
    'FORGET_PASSWORD_EMAIL_REQUIRED' => array(
      'message' => 'Email is required to reset password',
      'httpcode' => 400,
      'code' => 1010
    )
  );

  public static $PASSWORD_EMAIL_TEMPLATE = array(
    'FORGET_PASSWORD_EMAIL_TEMPLATE' => array(
      'message' => 'forgotEmailTemplate must be set',
      'httpcode' => 500
    )
  );

  public static $PASSWORD_EMAIL_PROVIDER = array(
    'FORGET_PASSWORD_EMAIL_PROVIDER' => array(
      'message' => 'EmailProvider is not properly implemented.  PHPCore and Mandrill can be used as defaults.',
      'httpcode' => 500
    )
  );
}