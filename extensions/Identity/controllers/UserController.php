<?php

namespace MABI\Identity;

use \MABI\RESTModelController;
use Slim\Exception\Stop;

/**
 * todo: docs
 *
 * @middleware \MABI\Middleware\RESTPostAndObjectOnlyAccess
 * @middleware \MABI\Identity\Middleware\SessionHeader
 * @middleware \MABI\Identity\Middleware\RESTOwnerOnlyAccess
 */
class UserController extends RESTModelController {

  /**
   * @var User
   */
  protected $model;

  function _restPostCollection() {
    if(empty($this->model->password) || strlen($this->model->password) < 6) {
      $this->getApp()->getSlim()->response()->status(400);
      throw new Stop("Password must be at least 6 characters");
    }

    if(empty($this->model->email)) {
      $this->getApp()->getSlim()->response()->status(400);
      throw new Stop("Email is required");
    }

    $user = User::init($this->getApp());
    $user->findByField('email', $this->model->email);
    $userId = $user->getId();

    if(!empty($userId)) {
      $this->getApp()->getSlim()->response()->status(409);
      throw new Stop('An account with this email already exists');
    }

    parent::_restPostCollection();

    $this->model->salt = uniqid(mt_rand(), true);
    $this->model->passHash = Identity::passHash($this->model->password, $this->model->salt);
    $this->model->save();
  }

  function postForgotPassword() {
     // todo: implement. get an email from post
  }
}
