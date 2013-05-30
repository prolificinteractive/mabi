<?php

namespace MABI\Identity;

include_once __DIR__ . '/../../../Model.php';

use MABI\Model;

class Session extends Model {
  /**
   * @var \DateTime
   * @field internal
   */
  public $created;

  /**
   * @var string
   * @field owner
   * @field internal
   */
  public $user;

  /**
   * @var string
   * @field external
   */
  public $email;

  /**
   * @var string
   * @field external
   */
  public $password;
}
