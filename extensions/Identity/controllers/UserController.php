<?php

namespace MABI\Identity;

include_once __DIR__ . '/../../../RESTModelController.php';

use \MABI\RESTModelController;

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

  protected $sessionModelClass = '\MABI\Identity\Session';

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
    $this->model = call_user_func($this->modelClass . '::init', $this->getApp());
    $this->model->loadParameters($this->getApp()->getSlim()->request()->post());

    if (empty($this->model->password) || strlen($this->model->password) < 6) {
      $this->getApp()->returnError('Password must be at least 6 characters', 400, 1004);
    }

    if (empty($this->model->email)) {
      $this->getApp()->returnError('Email is required', 400, 1005);
    }

    $user = User::init($this->getApp());
    $user->findByField('email', $this->model->email);
    $userId = $user->getId();

    if (!empty($userId)) {
      $this->getApp()->returnError('An account with this email already exists', 409, 1006);
    }

    Identity::insertUser($this->model);

    /**
     * Automatically creates a session for the newly created user
     *
     * @var $session Session
     */
    $session = call_user_func($this->sessionModelClass . '::init', $this->getApp());
    $session->created = new \DateTime('now');
    $session->lastAccessed = new \DateTime('now');
    $session->user = $this->model->getId();
    $session->insert();

    $this->model->newSessionId = $session->getId();
    echo $this->model->outputJSON();
  }

  public function postForgotPassword() {
    // todo: implement. get an email from post
  }
}
