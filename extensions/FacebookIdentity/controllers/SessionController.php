<?php

namespace MABI\FacebookIdentity;

include_once __DIR__ . '/../../../RESTModelController.php';
include_once __DIR__ . '/../../Identity/controllers/SessionController.php';

use MABI\Identity\Identity;

/**
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
class SessionController extends \MABI\Identity\SessionController {

  /**
   * @var \Mabi\Identity\Session
   */
  protected $model;

  protected $userModelClass = '\MABI\FacebookIdentity\User';

  /**
   * @var object
   */
  protected $mockData = NULL;

  /**
   * @var bool
   */
  protected $facebookOnly = FALSE;

  /**
   * @return boolean
   * @endpoint ignore
   */
  public function getFacebookOnly() {
    return $this->facebookOnly;
  }

  /**
   * @param boolean $facebookOnly
   */
  public function setFacebookOnly($facebookOnly) {
    $this->facebookOnly = $facebookOnly;
  }

  /**
   * Pulls the "Me" content from Facebook
   *
   * @param string $access_token The facebook connect access token
   *
   * @return mixed
   * @endpoint ignore
   */
  public function getFBInfo($access_token) {
    // If there is mock data for testing purposes, return this instead of contacting facebook
    if (!empty($this->mockData)) {
      return $this->mockData;
    }

    // todo: see if call was erroneous and throw exceptions
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me?access_token=' . $access_token);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    // Get the response and close the channel.
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response);
  }

  protected function insertFBUser($fbData) {
    /**
     * @var $userModel \MABI\FacebookIdentity\User
     */
    $userModel = call_user_func($this->userModelClass . '::init', $this->getApp());

    $userModel->firstName = $fbData->first_name;
    $userModel->lastName = $fbData->last_name;
    $userModel->email = $fbData->email;
    $userModel->password = uniqid();
    $userModel->facebookId = $fbData->id;

    Identity::insertUser($userModel);

    return $userModel;
  }

  /**
   * @docs-name Authenticate (Create New Session)
   *
   * Creates a session. Depending on whether the facebookOnly flag is set, the required POST fields will change.
   *
   * If facebookOnly is set, then this endpoint requires and only accepts the accessToken field
   *
   * If facebookOnly is NOT set, then this endpoint requires either both email and password to be set OR just
   * acceessToken
   *
   * If a Facebook accessToken is used and the user does not exist already in the API, a new user will be automatically
   * created and the returning newUserCreated field will be true.
   *
   * @docs-param email string body optional The email of the user to create the session for
   * @docs-param password string body optional The password of the user to create the session for
   * @docs-param accessToken string body optional The Facebook accessToken Facebook accessToken that is used to authenticate the user
   *
   * @throws \Slim\Exception\Stop
   */
  function _restPostCollection() {
    $this->model = call_user_func($this->modelClass . '::init', $this->getApp());
    $this->model->loadParameters($this->getApp()->getSlim()->request()->post());

    if (empty($this->model->accessToken)) {
      if ($this->getFacebookOnly()) {
        $this->getApp()->returnError('An authorization token is required to create a session', 400, 1000);
      }

      parent::_restPostCollection();
    }
    else {
      // get facebook info and login or create a user
      $fbData = $this->getFBInfo($this->model->accessToken);
      // todo: handle exceptions

      /**
       * @var $userModel \MABI\FacebookIdentity\User
       */
      $userModel = call_user_func($this->userModelClass . '::init', $this->getApp());
      $this->model->newUserCreated = FALSE;

      if (!$userModel->findByField('facebookId', $fbData->id)) {
        $userModel = $this->insertFBUser($fbData);
        $this->model->newUserCreated = TRUE;
      }

      $this->model->created = new \DateTime('now');
      $this->model->lastAccessed = new \DateTime('now');
      $this->model->user = $userModel->getId();
      $this->model->insert();
      echo $this->model->outputJSON();
    }
  }

  protected function getDocParameters(\ReflectionMethod $rMethod) {
    $docParameters = parent::getDocParameters($rMethod);

    if ($rMethod->getName() == '_restPostCollection' && $this->getFacebookOnly()) {
      // remove email & password if facebook only is enabled
      foreach ($docParameters as $k => $docParameter) {
        switch ($docParameter['Name']) {
          case 'email':
          case 'password':
            unset($docParameters[$k]);
            break;
          case 'accessToken':
            $docParameters[$k]['Required'] = 'Y';
        }
      }
    }

    return $docParameters;
  }


}
