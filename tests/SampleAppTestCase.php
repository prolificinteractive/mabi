<?php

namespace MABI\Testing;

include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/AppTestCase.php';

class SampleAppTestCase extends AppTestCase {

  public function setUpApp($env = array(), $withCache = false) {
    parent::setUpApp($env, $withCache);

    $this->app->setModelLoaders(array(new \MABI\DirectoryModelLoader('TestApp/TestModelDir', $this->app, 'mabiTesting')));
  }

}