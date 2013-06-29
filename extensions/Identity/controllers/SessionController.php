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
class SessionController extends RESTModelController {

  /**
   * @var \Mabi\Identity\Session
   */
  protected $model;

  protected $userModelClass = '\MABI\Identity\User';

  /**
   * Creates a session. A valid email and password of an existing user must be passed in, and the new session
   * (with the session id) will be returned.
   *
   * @docs-param email string body required todo: docs
   * @docs-param password string body required todo: docs
   *
   * @throws \Slim\Exception\Stop
   */
  function _restPostCollection() {
    $this->model = call_user_func($this->modelClass . '::init', $this->getApp());
    $this->model->loadParameters($this->getApp()->getSlim()->request()->post());

    if (empty($this->model->password) || empty($this->model->email)) {
      $this->getApp()->getSlim()->response()->status(400);
      throw new Stop("Email and Password are required to create a session");
    }

    /**
     * @var $user User
     */
    $user = call_user_func($this->userModelClass . '::init', $this->getApp());
    $user->findByField('email', $this->model->email);

    if ($user->passHash != Identity::passHash($this->model->password, $user->salt)) {
      $this->getApp()->getSlim()->response()->status(400);
      throw new Stop("Password is invalid");
    }

    $this->model->created = new \DateTime('now');
    $this->model->lastAccessed = new \DateTime('now');
    $this->model->user = $user->getId();
    $this->model->insert();
    echo $this->model->outputJSON();
  }
}
