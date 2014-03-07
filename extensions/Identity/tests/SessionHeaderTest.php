<?php

namespace MABI\Identity\Testing;

use MABI\Identity\Identity;
use MABI\Identity\Middleware\SessionHeader;
use MABI\RESTAccess\RESTAccess;
use MABI\Testing\MiddlewareTestCase;
use MABI\Testing\TableDefinition;

include_once __DIR__ . '/../../../vendor/autoload.php';
include_once __DIR__ . '/../../../tests/middleware/MiddlewareTestCase.php';
include_once __DIR__ . '/../Identity.php';
include_once __DIR__ . '/../../RESTAccess/RESTAccess.php';
include_once __DIR__ . '/../middleware/SessionHeader.php';

class SessionHeaderTest extends MiddlewareTestCase {

  protected static $SESSION_111444 = array(
    'created' => '1370663864',
    'userId' => 11
  );

  protected static $USER_11 = array(
    'id' => 11,
    'created' => 1372375580,
    'firstName' => 'Photis',
    'lastName' => 'Patriotis',
    'email' => 'ppatriotis@gmail.com',
    'passHash' => '604cefb585491865043db59f5f200c08af016dc636bcb37c858199e20f082c10',
    // result of: hash_hmac('sha256', '123', 'salt4456');
    'salt' => 'salt4456',
    'lastAccessed' => 1379430989
  );

  public function testCall() {
    $middleware = new SessionHeader();

    $this->setUpApp(array('PATH_INFO' => '/modelbs', 'SESSION' => '111444'), 'mabiTesting\ModelBController',
      array($middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('findOneByField')
      ->will($this->returnCallback(
        function ($field, $value, $table) {
          return $this->findOneByFieldCallback(
            array(
              'sessions' => new TableDefinition('id', 111444, self::$SESSION_111444),
              'users' => new TableDefinition('id', 11, self::$USER_11)
            ), $field, $value, $table);
        }
      ));

    // Makes sure that lastAccessed was updated on the user
    $user_11_mod = self::$USER_11;
    $today = new \DateTime('now');
    $user_11_mod['lastAccessed'] = $today->getTimestamp();
    $this->dataConnectionMock->expects($this->once())
      ->method('save')
      ->with('users', $user_11_mod, 'id', 11);
    
    $this->app->call();

    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getRequest()->session);
    $this->assertInstanceOf('\MABI\Identity\Session', $this->app->getRequest()->session);
    $this->assertEquals(11, $this->app->getRequest()->session->userId);
  }

  public function testDocs() {
    $middleware = new SessionHeader();

    $this->setUpApp(array('PATH_INFO' => '/modelbs', 'SESSION' => '111444'), 'mabiTesting\ModelBController',
      array($middleware));
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
