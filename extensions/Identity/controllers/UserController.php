<?php

namespace MABI\Identity;

include_once __DIR__ . '/../../../RESTModelController.php';

use \MABI\EmailSupport;
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
   * @var \MABI\EmailSupport\Provider
   */
  protected $emailProvider = null;

  /**
   * @var \MABI\EmailSupport\Template
   */
  protected $forgotEmailTemplate = null;

  /**
   * @return \MABI\EmailSupport\Provider
   * @endpoint ignore
   */
  public function getEmailProvider() {
    return $this->emailProvider;
  }

  /**
   * @param \MABI\EmailSupport\Provider $emailProvider
   */
  public function setEmailProvider($emailProvider) {
    $this->emailProvider = $emailProvider;
  }

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

    $this->model->insert();

    /**
     * Automatically creates a session for the newly created user
     *
     * @var $session Session
     */
    $session = call_user_func($this->sessionModelClass . '::init', $this->getApp());
    $session->user = $this->model;
    $session->insert();

    $this->model->newSessionId = $session->getId();
    echo $this->model->outputJSON();
  }

  /**
   * todo: docs
   *
   * @docs-param user string body required A user object to create in the database
   *
   * @param $id string The id of the user you are trying to update
   */
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


  public function postForgotPassword() {
    if ($this->getEmailProvider() == null) {
      $this->getApp()->returnError(array(
        'message' => 'EmailProvider is not properly implemented.  PHPCore and Mandrill can be used as defaults.',
        'httpcode' => 404
      ));
    }
    if ($this->forgotEmailTemplate == null) {
      $this->getApp()->returnError(array(
        'message' => 'forgotEmailTemplate must be set.',
        'httpcode' => 404
      ));
    }

    /**
     * @var $user User
     */
    $data = json_decode($this->getApp()->getRequest()->getBody());
    try {
      $email = $data->email;
    } catch (\Exception $e) {
      $this->getApp()->returnError(array(
        'message' => 'Email was not set.',
        'httpcode' => 404
      ));
    }
    $user = User::init($this->getApp());
    if (!$user->findByField('email', $email)) {
      $this->getApp()->returnError(array(
        'message' => 'There is no user with this email.',
        'httpcode' => 404
      ));
    }

    $user->lastAccessed = new \DateTime('now');
    $user->save();
    $authToken = Identity::passHash($user->passHash, $user->lastAccessed->getTimestamp());

    $this->forgotEmailTemplate->mergeData(array('!authToken' => $authToken));

    $resp = $this->getEmailProvider()->sendEmail(
      $user->email,
      $this->forgotEmailTemplate);

    echo json_encode($resp);
  }
}
