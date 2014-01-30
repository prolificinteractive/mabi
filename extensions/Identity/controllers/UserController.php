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
   * @param \MABI\EmailSupport\Template $forgotEmailTemplate
   */
  public function setForgotEmailTemplate($forgotEmailTemplate)
  {
    $this->forgotEmailTemplate = $forgotEmailTemplate;
  }

  /**
   * @return \MABI\EmailSupport\Template
   * @endpoint ignore
   */
  public function getForgotEmailTemplate()
  {
    return $this->forgotEmailTemplate;
  }

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
  public function post() {
    $this->model->loadFromExternalSource($this->getApp()->getRequest()->getBody());

    if (empty($this->model->password) || strlen($this->model->password) < 6) {
      $this->getApp()->returnError(Errors::$SHORT_PASSWORD);
    }

    if (empty($this->model->email)) {
      $this->getApp()->returnError(Errors::$EMAIL_REQUIRED);
    }

    if ($this->model->findByField('email', $this->model->email)) {
      $this->getApp()->returnError(Errors::$EMAIL_EXISTS);
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

    $oldUser = clone $this->model;
    $this->model->loadFromExternalSource($this->getApp()->getRequest()->getBody());

    if (!empty($this->model->password)) {
      if (strlen($this->model->password) < 6) {
        $this->getApp()->returnError(Errors::$SHORT_PASSWORD);
      }

      $this->model->passHash = Identity::passHash($this->model->password, $oldUser->salt);
      $this->model->password = NULL;

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

    if (empty($this->model->email)) {
      $this->getApp()->returnError(Errors::$EMAIL_REQUIRED);
    }

    if ($this->model->email != $oldUser->email && $this->model->findByField('email', $this->model->email)) {
      $this->getApp()->returnError(Errors::$EMAIL_EXISTS);
    }

    $this->model->created = $oldUser->created;
    $this->model->salt = $oldUser->salt;
    $this->model->lastAccessed = $oldUser->lastAccessed;
    $this->model->save();
    echo $this->model->outputJSON();
  }


  public function postForgotPassword() {
    if ($this->getEmailProvider() == null) {
      $this->getApp()->returnError(Errors::$PASSWORD_EMAIL_PROVIDER);
    }
    if ($this->forgotEmailTemplate == null) {
      $this->getApp()->returnError(Errors::$PASSWORD_EMAIL_TEMPLATE);
    }

    /**
     * @var $user User
     */
    $data = json_decode($this->getApp()->getRequest()->getBody());
    try {
      $email = $data->email;
    } catch (\Exception $e) {
      $this->getApp()->returnError(Errors::$PASSWORD_EMAIL_REQUIRED);
    }
    $user = User::init($this->getApp());
    if (!$user->findByField('email', $email)) {
      $this->getApp()->returnError(Errors::$PASSWORD_NO_USER_EMAIL);
    }

    $user->lastAccessed = new \DateTime('now');
    $user->save();
    $authToken = Identity::passHash($user->passHash, $user->lastAccessed->getTimestamp());

    $this->forgotEmailTemplate->mergeData(array('!authToken' => $authToken));

    $resp = $this->getEmailProvider()->sendEmail($user->email, $this->forgotEmailTemplate);

    echo json_encode($resp);
  }
}
