<?php

namespace MABI\Testing;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/MiddlewareTestCase.php';
include_once __DIR__ . '/../../middleware/SharedSecret.php';

class SharedSecretTest extends \MABI\Testing\MiddlewareTestCase {

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

    $this->app->getSlim()->call();

    $this->assertEquals(200, $this->app->getSlim()->response()->status());
    $this->assertNotEmpty($this->app->getSlim()->request()->apiApplication);
    $this->assertInstanceOf('\MABI\DefaultApplicationModel', $this->app->getSlim()->request()->apiApplication);
    $this->assertEquals('AppName', $this->app->getSlim()->request()->apiApplication->applicationName);
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