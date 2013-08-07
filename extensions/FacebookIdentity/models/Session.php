<?php

namespace MABI\FacebookIdentity;

include_once __DIR__ . '/../../Identity/models/Session.php';

class Session extends \MABI\Identity\Session {
  /**
   * Plaintext Facebook accessToken that is used to authenticate the user. This should only be filled for incoming
   * POSTs to create new sessions. Otherwise it will always be NULL.
   *
   * @var string
   * @field external
   */
  public $accessToken;

  /**
   * Authentication using a Facebook session can be done for Facebook users whom do not already have accounts
   * in the API. If the account for this session was created automatically, this field will return true only for
   * new session creation.
   *
   * @var bool
   * @field external
   */
  public $newUserCreated;
}
