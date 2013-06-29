<?php

namespace MABI\FacebookIdentity;

include_once __DIR__ . '/../../Identity/models/Session.php';

class Session extends \MABI\Identity\Session {
  /**
   * @var string
   * @field external
   */
  public $accessToken;

  /**
   * @var bool
   * @field external
   */
  public $newUserCreated;
}
