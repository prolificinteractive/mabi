<?php

namespace MABI\Identity;

include_once __DIR__ . '/../../../Model.php';

use MABI\Model;

class User extends Model {
  /**
   * @field owner
   * @var string
   */
  public $id;

  /**
   * @var \DateTime
   */
  public $created;

  /**
   * @var string
   */
  public $firstName;

  /**
   * @var string
   */
  public $lastName;

  /**
   * @var string
   */
  public $email;

  /**
   * @var string
   *
   * @field internal
   */
  public $passHash;

  /**
   * @var string
   *
   * @field internal
   */
  public $salt;

  /**
   * @var string
   *
   * @field external
   */
  public $password;

  /**
   * @var string
   *
   * @field external
   */
  public $newSessionId;
}
