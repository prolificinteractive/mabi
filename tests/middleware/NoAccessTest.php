<?php

namespace MABI\Testing;

use MABI\Middleware\NoAccess;

include_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/MiddlewareTestCase.php';
include_once __DIR__ . '/../../middleware/NoAccess.php';
include_once __DIR__ . '/../../middleware/SharedSecret.php';

class NoAccessTest extends MiddlewareTestCase {

  public function testStoppedCall() {
    $middleware = new NoAccess();
    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc'), 'mabiTesting\JustAController', array($middleware));

    $this->app->call();

    $this->assertEquals(401, $this->app->getResponse()->status());
  }

  public function testDocs() {
    $middleware = new NoAccess();
    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc'), 'mabiTesting\JustAController', array($middleware));

    $docArray = array(
      'parameters' => array(
        0 => array('Name' => 'shared-secret')
      )
    );
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $rRefMock = $this->getMock('\ReflectionMethod', array(), array(), '', FALSE);
    $middleware->documentMethod($rClassMock, $rRefMock, $docArray);
    $this->assertEmpty($docArray);
  }
}