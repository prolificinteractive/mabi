<?php

namespace mabiTesting;

use MABI\Controller;

include_once __DIR__ . '/../../../Controller.php';

/**
 * Class JustAController
 *
 * @middleware MABI\Middleware\AnonymousIdentifier
 * @package mabiTesting
 */
class JustAController extends Controller {
  public function post() {
    echo 'post called';
  }

  public function getTestFunc() {
    echo 'restGetTestFunc called';
  }

  public function postTestFunc() {
    return 'restPostTestFunc called';
  }

  public function putTestFunc() {
    return 'restPutTestFunc called';
  }

  public function deleteTestFunc() {
    return 'restDeleteTestFunc called';
  }

  public function getCustomError() {
    $this->getApp()->returnError(Errors::$TEST_NEW_ERROR, array('!replacement' => 'a replacement string'));
  }

  public function getCustomError2() {
    $this->getApp()->returnError(array('TEST_NEW_ERROR_2' => array(
      'message' => 'Test error2',
      'httpcode' => 401,
      'code' => 1
    )));
  }

  public function getCustomError3() {
    $this->getApp()->returnError('TEST_NEW_ERROR', array('!replacement' => 'a replacement string'));
  }
}