<?php

namespace MABI\Testing;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/MiddlewareTestCase.php';
include_once __DIR__ . '/../../middleware/AnonymousIdentifier.php';

class AnonymousIdentifierTest extends \MABI\Testing\MiddlewareTestCase {

  public function testCall() {
    $middleware = new \MABI\Middleware\AnonymousIdentifier();
    $this->setUpApp(array('ANONUUID' => 'test1', 'PATH_INFO' => '/justa/testfunc'), array($middleware));

    $this->app->getSlim()->call();

    $this->assertEquals('test1', $this->app->getSlim()->request()->anonymousId);
  }

  public function testDocs() {
    $middleware = new \MABI\Middleware\AnonymousIdentifier();
    $this->setUpApp(array('ANONUUID' => 'test1', 'PATH_INFO' => '/justa/testfunc'), array($middleware));

    $docArray = array();
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $rRefMock = $this->getMock('\ReflectionMethod', array(), array(), '', FALSE);
    $middleware->documentMethod($rClassMock, $rRefMock, $docArray);
    $this->assertNotEmpty($docArray);
  }
}