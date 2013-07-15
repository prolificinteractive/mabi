<?php

namespace MABI\Testing;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/MiddlewareTestCase.php';
include_once __DIR__ . '/../../middleware/APIApplicationOnlyAccess.php';
include_once __DIR__ . '/../../middleware/SharedSecret.php';

class APIApplicationOnlyAccessTest extends MiddlewareTestCase {

  public function testStoppedCall() {
    $middleware = new \MABI\Middleware\SharedSecret();
    $middleware2 = new \MABI\Middleware\APIApplicationOnlyAccess();
    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc'), array($middleware, $middleware2));

    $this->app->call();

    $this->assertEquals(401, $this->app->getResponse()->status());
  }

  public function testPassedCall() {
    $middleware = new \MABI\Middleware\SharedSecret();
    $middleware2 = new \MABI\Middleware\APIApplicationOnlyAccess();

    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc', 'SHARED_SECRET' => 'TEST-SECRET-1'),
      array($middleware, $middleware2));

    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('sharedSecret', 'TEST-SECRET-1', 'applications', array())
      ->will($this->returnValue(array(
        'applicationName' => 'AppName',
        'sharedSecret' => 'TEST-SECRET-1'
      )));

    $this->app->call();

    $this->assertEquals(200, $this->app->getResponse()->status());
  }

  public function testDocs() {
    $middleware = new \MABI\Middleware\APIApplicationOnlyAccess();
    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc'), array($middleware));

    $docArray = array(
      'parameters' => array(
        0 => array('Name' => 'shared-secret')
      )
    );
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $rRefMock = $this->getMock('\ReflectionMethod', array(), array(), '', FALSE);
    $middleware->documentMethod($rClassMock, $rRefMock, $docArray);
    $this->assertNotEmpty($docArray);
  }
}