<?php

namespace MABI\RESTAccess\Testing;

include_once __DIR__ . '/../../../vendor/autoload.php';
include_once __DIR__ . '/../../../tests/middleware/MiddlewareTestCase.php';
include_once __DIR__ . '/../PostOnly.php';

use \MABI\RESTAccess\PostOnly;
use \MABI\Testing\MiddlewareTestCase;

class PostOnlyTest extends MiddlewareTestCase {

  public function testStoppedCall() {
    $middleware = new PostOnly();
    $this->setUpApp(array('PATH_INFO' => '/modelbs'), array($middleware));

    $this->app->call();

    $this->assertEquals(401, $this->app->getResponse()->status());
  }

  public function testPassedCall() {
    $middleware = new PostOnly();
    $this->setUpApp(array(
      'PATH_INFO' => '/modelbs',
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"name":"modelb"}',
    ), array($middleware));

    $this->dataConnectionMock->expects($this->once())
      ->method('insert')
      ->with('modelbs', array(
        'name' => 'modelb',
        'testOwner' => NULL
      ))
      ->will($this->returnValue(array(
        array(
          'modelBId' => 2,
          'name' => 'modelb'
        )
      )));

    $this->app->call();

    $this->assertEquals(200, $this->app->getResponse()->status());
  }

  public function testSkipDocs() {
    $middleware = new PostOnly();
    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc'), array($middleware));

    $docArray = array(
      'HTTPMethod' => 'test',
      'URI' => "/test",
      'Synopsis' => '',
      'parameters' => array()
    );
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $reflectionMethod = new \ReflectionMethod(get_class($this->restController), 'put');

    $middleware->documentMethod($rClassMock, $reflectionMethod, $docArray);
    $this->assertNull($docArray);
  }

  public function testFullDocs() {
    $middleware = new PostOnly();
    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc'), array($middleware));

    $docArray = array(
      'HTTPMethod' => 'test',
      'URI' => "/test",
      'Synopsis' => '',
      'parameters' => array()
    );
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $reflectionMethod = new \ReflectionMethod(get_class($this->restController), 'post');

    $middleware->documentMethod($rClassMock, $reflectionMethod, $docArray);
    $this->assertNotEmpty($docArray);
  }
}