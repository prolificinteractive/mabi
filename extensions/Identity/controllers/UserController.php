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

    /**
     * Automatically creates a session for the newly created user
     *
     * @var $session Session
     */
    $session = call_user_func($this->sessionModelClass . '::init', $this->getApp());
    $session->created = new \DateTime('now');
    $session->lastAccessed = new \DateTime('now');
    $session->user = $user->getId();
    $session->insert();

    Identity::insertUser($this->model);
    $this->model->newSessionId = $session->getId();
    echo $this->model->outputJSON();
  }

  public function postForgotPassword() {
    // todo: implement. get an email from post
  }
}
