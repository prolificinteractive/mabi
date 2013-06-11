<?php

namespace MABI\Identity;

include_once __DIR__ . '/../../../RESTModelController.php';

use \MABI\RESTModelController;
use Slim\Exception\Stop;

/**
 * todo: docs
 *
 * @middleware \MABI\RESTAccess\PostAndObjectOnly
 * @middleware \MABI\Identity\Middleware\SessionHeader
 * @middleware \MABI\Identity\Middleware\RESTOwnerOnlyAccess
 */
class UserController extends RESTModelController {

  /**
   * @var \MABI\Identity\User
   */
  protected $model;

  /**
   * todo: docs
   *
   * @docs-param firstName string body optional todo: docs
   * @docs-param lastName string body optional todo: docs
   * @docs-param email string body required todo: docs
   * @docs-param password string body required todo: docs
   *
   * @throws \Slim\Exception\Stop
   */
  function _restPostCollection() {
    $this->model = call_user_func($this->modelClass . '::init', $this->getApp());
    $this->model->loadParameters($this->getApp()->getSlim()->request()->post());

    if (empty($this->model->password) || strlen($this->model->password) < 6) {
      $this->getApp()->getSlim()->response()->status(400);
      throw new Stop("Password must be at least 6 characters");
    }

    if (empty($this->model->email)) {
      $this->getApp()->getSlim()->response()->status(400);
      throw new Stop("Email is required");
    }

    $user = User::init($this->getApp());
    $user->findByField('email', $this->model->email);
    $userId = $user->getId();

    if (!empty($userId)) {
      $this->getApp()->getSlim()->response()->status(409);
      throw new Stop('An account with this email already exists');
    }

    $this->model->salt = uniqid(mt_rand(), TRUE);
    $this->model->passHash = Identity::passHash($this->model->password, $this->model->salt);
    $this->model->password = NULL;
    $this->model->created = new \DateTime('now');
    $this->model->insert();
    echo $this->model->outputJSON();
  }

  function postForgotPassword() {
    // todo: implement. get an email from post
  }
}
