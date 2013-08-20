<?php

namespace MABI\Identity;

include_once __DIR__ . '/../../../Model.php';

use MABI\Model;

class User extends Model {

  /**
   * @field owner
   * @field id
   * @var string
   */
  public $userId;

  /**
   * When the user was created
   *
   * @var \DateTime
   */
  public $created;

  /**
   * First name of the user
   *
   * @var string
   */
  public $firstName;

  /**
   * Last name of the user
   *
   * @var string
   */
  public $lastName;

  /**
   * Email address of the user
   *
   * @var string
   */
  public $email;

  /**
   * Stores a hash of the password + salt. Typically we use Identity::passHash() to generate it
   *
   * @var string
   *
   * @field internal
   */
  public $passHash;

  /**
   * A random salt assigned to each user to keep password hashes unique
   *
   * @var string
   *
   * @field internal
   */
  public $salt;

  /**
   * A plaintext transmission of the user's password. Should only be sent when creating or updating the password. Will
   * always be returned as NULL. Passwords must be at least 6 characters long and are stored as secured hashes.
   *
   * @var string
   *
   * @field external
   */
  public $password;

  /**
   * A session id that is only filled when creating a new user, so that they may authenticate immediately.
   *
   * @var string
   *
   * @field external
   */
  public $newSessionId;

  public function insert() {
    $this->salt = uniqid(mt_rand(), TRUE);
    $this->passHash = Identity::passHash($this->password, $this->salt);
    $this->password = NULL;
    $this->created = new \DateTime('now');
    parent::insert();
  }
}
