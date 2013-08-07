<?php

namespace MABI\FacebookIdentity;

include_once __DIR__ . '/../../Identity/models/User.php';

class User extends \MABI\Identity\User {
  /**
   * Facebook ID of the user
   *
   * @var string
   */
  public $facebookId;
}
