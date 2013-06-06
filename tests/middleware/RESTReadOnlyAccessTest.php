<?php

namespace MABI\Testing;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/MiddlewareTestCase.php';
include_once __DIR__ . '/../../middleware/RESTReadOnlyAccess.php';

use \MABI\Middleware\RESTReadOnlyAccess;

class RESTReadOnlyAccessTest extends MiddlewareTestCase {

  public function testStoppedCall() {
    $middleware = new RESTReadOnlyAccess();
    $this->setUpRESTApp(array('PATH_INFO' => '/modelb','REQUEST_METHOD' => 'POST'), array($middleware));

    $this->app->call();

    $this->assertEquals(401, $this->app->getSlim()->response()->status());
  }

  public function testPassedCall() {
    $middleware = new RESTReadOnlyAccess();
    $this->setUpRESTApp(array('PATH_INFO' => '/modelb/1'), array($middleware));

    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('id', 1, 'modelbs')
      ->will($this->returnValue(array(
        'modelBId' => 1,
        'name' => 'test'
      )));

    $this->app->call();

    $this->assertEquals(200, $this->app->getSlim()->response()->status());
  }

  public function testSkipDocs() {
    $middleware = new RESTReadOnlyAccess();
    $this->setUpRESTApp(array('PATH_INFO' => '/justa/testfunc'), array($middleware));

    $docArray = array(
      'HTTPMethod' => 'test',
      'URI' => "/test",
      'Synopsis' => '',
      'parameters' => array()
    );
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $reflectionMethod = new \ReflectionMethod(get_class($this->restController),
      '_restPostCollection');

    $middleware->documentMethod($rClassMock, $reflectionMethod, $docArray);
    $this->assertNull($docArray);
  }

  public function testFullDocs() {
    $middleware = new RESTReadOnlyAccess();
    $this->setUpRESTApp(array('PATH_INFO' => '/justa/testfunc'), array($middleware));

    $docArray = array(
      'HTTPMethod' => 'test',
      'URI' => "/test",
      'Synopsis' => '',
      'parameters' => array()
    );
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $reflectionMethod = new \ReflectionMethod(get_class($this->restController),
      '_restGetCollection');

    $middleware->documentMethod($rClassMock, $reflectionMethod, $docArray);
    $this->assertNotEmpty($docArray);
  }
}