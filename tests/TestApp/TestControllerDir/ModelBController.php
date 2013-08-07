<?php

namespace mabiTesting;

include_once __DIR__ . '/../../../RESTModelController.php';

class ModelBController extends \MABI\RESTModelController {
  public function getSearch() {

  }

  public function restGetTestFunc() {
    return 'restGetTestFunc called';
  }

  public function restPostTestFunc() {
    return 'restPostTestFunc called';
  }

  public function restPutTestFunc() {
    return 'restPutTestFunc called';
  }

  public function restDeleteTestFunc() {
    return 'restDeleteTestFunc called';
  }
}