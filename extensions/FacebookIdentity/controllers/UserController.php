<?php

namespace MABI\FacebookIdentity;

include_once __DIR__ . '/../../Identity/controllers/UserController.php';

use MABI\Parser;

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
class UserController extends \MABI\Identity\UserController {

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
   * @docs-name Create New User
   *
   * Creates a new user. Will pass back the created user model, and will also create a new session (in newSessionId)
   * so that the user may authenticate immediately.
   *
   * Facebook Connect users cannot be created through this endpoint, so if the facebookOnly flag is set, this method
   * will be disabled.
   *
   * @docs-param firstName string body optional The first name of the new user
   * @docs-param lastName string body optional The last name of the new user
   * @docs-param email string body required The email address of the new user. This must be unique in the database.
   * @docs-param password string body required The password for the new user. Please see requirements in the Model.
   *
   * @throws \Slim\Exception\Stop
   */
  public function post() {
    if ($this->getFacebookOnly()) {
      $this->getApp()->returnError('Facebook Connect is the only method allowed to create users', 401, 1001);
    }
    else {
      parent::post();
    }
  }

  public function postForgotPassword() {
    if ($this->getFacebookOnly()) {
      $this->getApp()->returnError('Facebook Connect is the only method allowed to create users', 401, 1001);
    }
    else {
      parent::postForgotPassword();
    }
  }

  /**
   * @endpoint ignore
   *
   * @param Parser $parser
   *
   * @return array
   */
  public function getDocJSON(Parser $parser) {
    $doc = parent::getDocJSON($parser);

    // Removes documentation for post and postForgotPassword if facebookOnly is set
    if ($this->getFacebookOnly()) {
      foreach ($doc['methods'] as $k => $method) {
        if ($method['InternalMethodName'] == 'post' ||
          $method['InternalMethodName'] == 'postForgotPassword'
        ) {
          unset($doc['methods'][$k]);
          continue;
        }
      }
    }

    return $doc;
  }
}
