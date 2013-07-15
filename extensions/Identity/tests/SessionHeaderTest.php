<?php

namespace MABI\Identity\Testing;

use MABI\Identity\Identity;
use MABI\Identity\Middleware\SessionHeader;
use MABI\RESTAccess\RESTAccess;
use MABI\Testing\MiddlewareTestCase;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../../../tests/middleware/MiddlewareTestCase.php';
include_once __DIR__ . '/../Identity.php';
include_once __DIR__ . '/../../RESTAccess/RESTAccess.php';
include_once __DIR__ . '/../middleware/SessionHeader.php';

class SessionHeaderTest extends MiddlewareTestCase {

  public function testCall() {
    $middleware = new SessionHeader();

    $this->setUpRESTApp(array('PATH_INFO' => '/modelbs', 'SESSION' => 'TEST-SESSION-ID-1'), array($middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('id', 'TEST-SESSION-ID-1', 'sessions', array())
      ->will($this->returnValue(array(
        'created' => '1370663864',
        'user' => 'TEST-USER-ID-1'
      )));

    $this->app->call();

    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getRequest()->session);
    $this->assertInstanceOf('\MABI\Identity\Session', $this->app->getRequest()->session);
    $this->assertEquals('TEST-USER-ID-1', $this->app->getRequest()->session->user);
  }

  public function testDocs() {
    $middleware = new SessionHeader();

    $this->setUpRESTApp(array('PATH_INFO' => '/modelbs', 'SESSION' => 'TEST-SESSION-ID-1'), array($middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $docArray = array();
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $rRefMock = $this->getMock('\ReflectionMethod', array(), array(), '', FALSE);
    $middleware->documentMethod($rClassMock, $rRefMock, $docArray);

    $this->assertInternalType('array', $docArray);
    $this->assertNotEmpty('array', $docArray['parameters']);
    $this->assertNotCount(0, $docArray['parameters']);

    $sessionFound = FALSE;
    foreach ($docArray['parameters'] as $parameterDoc) {
      if (is_array($parameterDoc) && $parameterDoc['Name'] == 'SESSION' && $parameterDoc['Location'] == 'header') {
        $sessionFound = TRUE;
        break;
      }
    }

    $this->assertTrue($sessionFound);
  }
}