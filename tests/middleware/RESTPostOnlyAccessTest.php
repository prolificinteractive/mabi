<?php

namespace MABI\Testing;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/MiddlewareTestCase.php';
include_once __DIR__ . '/../../middleware/RESTPostOnlyAccess.php';

use \MABI\Middleware\RESTPostOnlyAccess;

class RESTPostOnlyAccessTest extends MiddlewareTestCase {

  public function testStoppedCall() {
    $middleware = new RESTPostOnlyAccess();
    $this->setUpRESTApp(array('PATH_INFO' => '/modelb'), array($middleware));

    $this->app->getSlim()->call();

    $this->assertEquals(401, $this->app->getSlim()->response()->status());
  }

  public function testPassedCall() {
    $middleware = new RESTPostOnlyAccess();
    $this->setUpRESTApp(array(
      'PATH_INFO' => '/modelb',
      'REQUEST_METHOD' => 'POST',
      'slim.input' => 'name=modelb',
    ), array($middleware));

    $this->dataConnectionMock->expects($this->once())
      ->method('insert')
      ->with('modelbs', array('name' => 'modelb'))
      ->will($this->returnValue(array(
        array(
          'modelBId' => 2,
          'name' => 'modelb'
        )
      )));

    $this->app->getSlim()->call();

    $this->assertEquals(200, $this->app->getSlim()->response()->status());
  }

  public function testSkipDocs() {
    $middleware = new RESTPostOnlyAccess();
    $this->setUpRESTApp(array('PATH_INFO' => '/justa/testfunc'), array($middleware));

    $docArray = array();
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $reflectionMethod = new \ReflectionMethod(get_class($this->restController),
      '_restPutCollection');

    $middleware->documentMethod($rClassMock, $reflectionMethod, $docArray);
    $this->assertNull($docArray);
  }

  public function testFullDocs() {
    $middleware = new RESTPostOnlyAccess();
    $this->setUpRESTApp(array('PATH_INFO' => '/justa/testfunc'), array($middleware));

    $docArray = array();
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $reflectionMethod = new \ReflectionMethod(get_class($this->restController),
      '_restPostCollection');

    $middleware->documentMethod($rClassMock, $reflectionMethod, $docArray);
    $this->assertNotEmpty($docArray);
  }
}