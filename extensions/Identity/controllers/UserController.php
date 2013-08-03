<?php

namespace MABI\Identity;

include_once __DIR__ . '/../../../RESTModelController.php';

use \MABI\RESTModelController;

/**
 * @docs show-model
 *
 * Manages the endpoints for the User model. This includes creating a new user using a POST to the collection, and
 * getting, updating and deleting the user information.
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
   * @docs-name Create New User
   *
   * Creates a new user. Will pass back the created user model, and will also create a new session (in newSessionId)
   * so that the user may authenticate immediately.
   *
   * @docs-param user string body required A user object to create in the database
   *
   * @throws \Slim\Exception\Stop
   */
  public function _restPostCollection() {
    $this->model->loadFromExternalSource($this->getApp()->getRequest()->getBody());

    if (empty($this->model->password) || strlen($this->model->password) < 6) {
      $this->getApp()->returnError('Password must be at least 6 characters', 400, 1004);
    }

    if (empty($this->model->email)) {
      $this->getApp()->returnError('Email is required', 400, 1005);
    }

    if ($this->model->findByField('email', $this->model->email)) {
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
    $session->user = $this->model;
    $session->userId = $this->model->getId();
    $session->insert();

    $this->model->newSessionId = $session->getId();
    echo $this->model->outputJSON();
  }

  public function _restPutResource($id) {
    $updatedUser = call_user_func($this->modelClass . '::init', $this->getApp());
    $updatedUser->loadFromExternalSource($this->getApp()->getRequest()->getBody());
    $updatedUser->setId($id);

    if (!empty($updatedUser->password)) {
      if (strlen($updatedUser->password) < 6) {
        $this->getApp()->returnError('Password must be at least 6 characters', 400, 1004);
      }

      $updatedUser->passHash = Identity::passHash($updatedUser->password, $this->model->salt);
      $updatedUser->password = NULL;

      /**
       * Deletes all sessions except for the current one for the user whose password changed
       *
       * @var $session Session
       */
      $session = call_user_func($this->sessionModelClass . '::init', $this->getApp());

      $deleteSessions = $session->findAllByField('userId', $id);
      foreach ($deleteSessions as $session) {
        if ($session->sessionId == $this->getApp()->getRequest()->session->sessionId) {
          continue;
        }
        $session->delete();
      }
    }
    else {
      $updatedUser->passHash = $this->model->passHash;
    }

    if (empty($updatedUser->email)) {
      $this->getApp()->returnError('Email is required', 400, 1005);
    }

    if ($updatedUser->email != $this->model->email && $updatedUser->findByField('email', $updatedUser->email)) {
      $this->getApp()->returnError('An account with this email already exists', 409, 1006);
    }

    $updatedUser->created = $this->model->created;
    $updatedUser->salt = $this->model->salt;

    $updatedUser->save();
    echo $updatedUser->outputJSON();
  }

  /**
   * @endpoint ignore
   */
  public function postForgotPassword() {
    // todo: implement. get an email from post
  }
}
