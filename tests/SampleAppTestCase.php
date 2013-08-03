<?php

namespace MABI\Testing;

include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/AppTestCase.php';

class SampleAppTestCase extends AppTestCase {

  public function setUpApp($env = array()) {
    parent::setUpApp($env);

    $this->app->setModelLoaders(array(new \MABI\DirectoryModelLoader('TestApp/TestModelDir', 'mabiTesting')));
  }

}