<?php

namespace MABI\FacebookIdentity;

include_once __DIR__ . '/../../Identity/controllers/UserController.php';

use MABI\Parser;

/**
 * @Docs\ShowModel
 *
 * Manages the endpoints for the User model. This includes creating a new user using a POST to the collection, and
 * getting, updating and deleting the user information.
 *
 * @middleware("\MABI\RESTAccess\PostAndObjectOnly")
 * @middleware("\MABI\Identity\Middleware\SessionHeader")
 * @middleware("\MABI\Identity\Middleware\RESTOwnerOnlyAccess")
 */
class UserController extends \MABI\Identity\UserController {

  /**
   * @var bool
   */
  protected $facebookOnly = FALSE;

  /**
   * @return boolean
   * @Endpoint\Ignore
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
   * Creates a new user.  Sending an `email` and `password` is required. Any other modifiable fields may be
   * included and the user will be initiated with them.
   *
   * Facebook Connect users cannot be created through this endpoint, so if the facebookOnly flag is set, this method
   * will be disabled.
   *
   * Sample Request:
   * ~~~
   * {
   *     "firstName": "optional",
   *     "lastName": "optional",
   *     "email": "this@isrequir.ed",
   *     "password": "required"
   * }
   * ~~~
   *
   * The response returns the created user model. It creates a session automatically and returns `newSessionId` which
   * allows you to immediately begin making authenticated calls. The `userId` of the authenticated user is also
   * returned.
   *
   * Sample Response:
   * ~~~
   * {
   *     "userId": "5159bfde68dca0c173f0939f",
   *     "created": 1391479853,
   *     "firstName": "optional",
   *     "lastName": "optional",
   *     "email": "this@isrequir.ed",
   *     "newSessionId": "52f04c2d5a8b496a3f000001"
   * }
   * ~~~
   *
   * @Docs\Param("user",type="string",location="body",required=true,description="A user object to create in the database")
   *
   * @throws \Slim\Exception\Stop
   */
  public function post() {
    if ($this->getFacebookOnly()) {
      $this->getApp()->returnError(Errors::$FB_ONLY);
    }
    else {
      parent::post();
    }
  }

  /**
   * @docs-name  Email forgot password token
   *
   * json should be passed in in the following form. Does not work with Facebook Connect
   * ~~~
   * {
   *     "email": string
   * }
   * ~~~
   *
   * @Docs\Param("email",type="string",location="body",required=true,description="json object containing a user's email")
   */
  public function postForgotPassword() {
    if ($this->getFacebookOnly()) {
      $this->getApp()->returnError(Errors::$FB_ONLY);
    }
    else {
      parent::postForgotPassword();
    }
  }

  /**
   * @Endpoint\Ignore
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
