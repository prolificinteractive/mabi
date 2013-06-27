<?php

namespace MABI\FacebookIdentity;

use MABI\Parser;
use Slim\Exception\Stop;

/**
 * todo: docs
 *
 * @middleware \MABI\RESTAccess\PostAndObjectOnly
 * @middleware \MABI\Identity\Middleware\SessionHeader
 * @middleware \MABI\Identity\Middleware\RESTOwnerOnlyAccess
 */
class UserController extends \MABI\Identity\UserController {
  /**
   * Creates a new user. Will pass back the created user model
   *
   * @docs-param firstName string body optional todo: docs
   * @docs-param lastName string body optional todo: docs
   * @docs-param email string body required todo: docs
   * @docs-param password string body required todo: docs
   *
   * @throws \Slim\Exception\Stop
   */
  public function _restPostCollection() {
    if ($this->getExtension()->getFacebookOnly()) {
      $this->getApp()->getSlim()->response()->status(401);
      throw new Stop("Facebook Connect is the only method allowed to create users");
    }
    else {
      parent::_restPostCollection();
    }
  }

  public function postForgotPassword() {
    if ($this->getExtension()->getFacebookOnly()) {
      $this->getApp()->getSlim()->response()->status(401);
      throw new Stop("Facebook Connect is the only method allowed to authenticate users");
    }
    else {
      // todo: implement. get email from post, warn if a facebook user
      parent::_restPostCollection();
    }
  }

  public function getDocJSON(Parser $parser) {
    if ($this->getExtension()->getFacebookOnly()) {
      return array();
    }
    else {
      return parent::getDocJSON($parser);
    }
  }
}
