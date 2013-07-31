<?php

namespace MABI\Identity;

include_once __DIR__ . '/../../../RESTModelController.php';

use \MABI\RESTModelController;

/**
 * @docs show-model
 *
 * Manages the endpoints for the maintaining authenticated sessions for Users. These are required for many
 * calls that secure user information or must identify the user. The endpoints include creating a new session
 * using a POST to the collection, and getting, updating and deleting extra session information.
 *
 * Authenticating into the API is analogous to creating a new session, as logging out of the API is analogous to
 * deleting the session.
 *
 * There is no expiration mechanism built into the sessions, but this can be done in a custom implementation.
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
   * @docs-name Authenticate (Create New Session)
   *
   * Creates a session. A valid email and password of an existing user must be passed in, and the new session
   * (with the session id) will be returned.
   *
   * @docs-param session string body required A session object (with email & password filled in)
   *
   * @throws \Slim\Exception\Stop
   */
  function _restPostCollection() {
    $this->model = call_user_func($this->modelClass . '::init', $this->getApp());
    $this->model->loadFromExternalSource($this->getApp()->getRequest()->getBody());

    if (empty($this->model->password) || empty($this->model->email)) {
      $this->getApp()->returnError('Email and Password are required to create a session', 400, 1002);
    }

    /**
     * @var $user User
     */
    $user = call_user_func($this->userModelClass . '::init', $this->getApp());
    $user->findByField('email', $this->model->email);

    if ($user->passHash != Identity::passHash($this->model->password, $user->salt)) {
      $this->getApp()->returnError('Password is invalid', 400, 1003);
    }

    $this->model->created = new \DateTime('now');
    $this->model->lastAccessed = new \DateTime('now');
    $this->model->user = $user;
    $this->model->userId = $user->getId();
    $this->model->email = NULL;
    $this->model->password = NULL;
    $this->model->insert();
    echo $this->model->outputJSON();
  }
}
