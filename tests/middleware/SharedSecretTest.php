<?php

namespace MABI\Testing;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/MiddlewareTestCase.php';
include_once __DIR__ . '/../../middleware/SharedSecret.php';

class SharedSecretTest extends MiddlewareTestCase {

  public function testCall() {
    $middleware = new \MABI\Middleware\SharedSecret();

    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc', 'SHARED_SECRET' => 'TEST-SECRET-1'),
      array($middleware));

    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('sharedSecret', 'TEST-SECRET-1', 'applications', array())
      ->will($this->returnValue(array(
        'applicationName' => 'AppName',
        'sharedSecret' => 'TEST-SECRET-1'
      )));

    $this->app->call();

    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getRequest()->apiApplication);
    $this->assertInstanceOf('\MABI\DefaultApplicationModel', $this->app->getRequest()->apiApplication);
    $this->assertEquals('AppName', $this->app->getRequest()->apiApplication->applicationName);
  }

  // todo: test custom application class
  // todo: test custom share secret field

  public function testDocs() {
    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc'));

    $middleware = new \MABI\Middleware\SharedSecret();
    $docArray = array();
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $rRefMock = $this->getMock('\ReflectionMethod', array(), array(), '', FALSE);
    $middleware->documentMethod($rClassMock, $rRefMock, $docArray);
    $this->assertNotEmpty($docArray);
  }
}