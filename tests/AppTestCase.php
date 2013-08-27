<?php

namespace MABI\Testing;

use MABI\App;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/MockDataConnection.php';

class AppTestCase extends \PHPUnit_Framework_TestCase {

  /**
   * @var \MABI\App
   */
  protected $app;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $dataConnectionMock;

  public function setUpApp($env = array()) {
    \Slim\Environment::mock($env);
    $this->app = new App();

    $this->dataConnectionMock = $this->getMock('\MABI\Testing\MockDataConnection',
      array(
        'findOneByField',
        'query',
        'insert',
        'save',
        'deleteByField',
        'clearAll',
        'getNewId',
        'findAll',
        'findAllByField'
      )
    );

    $this->app->addDataConnection('default', $this->dataConnectionMock);
  }

}