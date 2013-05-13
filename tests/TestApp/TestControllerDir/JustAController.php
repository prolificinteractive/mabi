<?php

namespace mabiTesting;

include_once __DIR__ . '/../../../Controller.php';

/**
 * Class JustAController
 *
 * @middleware MABI\Middleware\AnonymousIdentifier
 * @package mabiTesting
 */
class JustAController extends \MABI\Controller {
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
}