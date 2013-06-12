<?php

namespace MABI\Testing;

use MABI\Identity\Identity;
use MABI\Identity\Middleware\SessionHeader;
use MABI\RESTAccess\RESTAccess;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../../../../tests/middleware/MiddlewareTestCase.php';
include_once __DIR__ . '/../../Identity.php';
include_once __DIR__ . '/../../../RESTAccess/RESTAccess.php';
include_once __DIR__ . '/../../middleware/SessionHeader.php';

class SessionHeaderTest extends MiddlewareTestCase {

  public function testCall() {
    $middleware = new SessionHeader();

    $this->setUpRESTApp(array('PATH_INFO' => '/modelbs', 'SESSION' => 'TEST-SESSION-ID-1'), array($middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);
    $identity->getModelClasses();

    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('id', 'TEST-SESSION-ID-1', 'sessions', array())
      ->will($this->returnValue(array(
        'created' => '1370663864',
        'user' => 'TEST-USER-ID-1'
      )));

    $this->app->call();

    $this->assertEquals(200, $this->app->getSlim()->response()->status());
    $this->assertNotEmpty($this->app->getSlim()->request()->session);
    $this->assertInstanceOf('\MABI\Identity\Session', $this->app->getSlim()->request()->session);
    $this->assertEquals('TEST-USER-ID-1', $this->app->getSlim()->request()->session->user);
  }
}