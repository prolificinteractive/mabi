<?php

namespace MABI\Identity\Testing;

use MABI\Autodocs\MarkdownParser;
use MABI\Identity\Identity;
use MABI\Identity\Middleware\RESTOwnerOnlyAccess;
use MABI\Identity\Middleware\SessionHeader;
use MABI\RESTAccess\RESTAccess;
use MABI\Testing\MiddlewareTestCase;
use MABI\Testing\TableDefinition;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../../../tests/middleware/MiddlewareTestCase.php';
include_once __DIR__ . '/../../../autodocs/MarkdownParser.php';
include_once __DIR__ . '/../Identity.php';
include_once __DIR__ . '/../middleware/RESTOwnerOnlyAccess.php';
include_once __DIR__ . '/../middleware/SessionHeader.php';

class RESTOwnerOnlyAccessTest extends MiddlewareTestCase {

  protected static $SESSION_111444 = array(
    'created' => '1370663864',
    'userId' => 11
  );

  protected static $SESSION_111445 = array(
    'created' => '1370663864',
    'userId' => 12
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

  protected static $USER_12 = array(
    'id' => 12,
    'created' => 1372375580,
    'firstName' => 'Photis',
    'lastName' => 'Patriotis',
    'email' => 'ppatriotis@gmail.com',
    'passHash' => '604cefb585491865043db59f5f200c08af016dc636bcb37c858199e20f082c10',
    // result of: hash_hmac('sha256', '123', 'salt4456');
    'salt' => 'salt4456',
    'lastAccessed' => 1379430989
  );

  protected static $MODELB_1 = array('modelBId' => 1, 'name' => 'test', 'testOwner' => 11);

  public function testSuccessfulCall() {
    $middleware = new RESTOwnerOnlyAccess();

    $this->setUpApp(array('PATH_INFO' => '/modelbs/4', 'SESSION' => '111444'),
      array(new SessionHeader(), $middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->exactly(3))
      ->method('findOneByField')
      ->will($this->returnCallback(
        function ($field, $value, $table) {
          return $this->findOneByFieldCallback(
            array(
              'sessions' => new TableDefinition('id', 111444, self::$SESSION_111444),
              'users' => new TableDefinition('id', 11, self::$USER_11),
              'modelbs' => new TableDefinition('id', 4, self::$MODELB_1)
            ), $field, $value, $table);
        }
      ));

    $this->app->call();

    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $output = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($output);
    $this->assertEquals('test', $output->name);
  }

  public function testNoSessionCall() {
    $middleware = new RESTOwnerOnlyAccess();

    $this->setUpApp(array('PATH_INFO' => '/modelbs/4'),
      array($middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->app->call();

    $this->assertEquals(401, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $this->assertEquals(1007, json_decode($this->app->getResponse()->body())->error->code);
  }

  public function testWrongOwnerCall() {
    $middleware = new RESTOwnerOnlyAccess();

    $this->setUpApp(array('PATH_INFO' => '/modelbs/4', 'SESSION' => '111445'),
      array(new SessionHeader(), $middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->exactly(3))
      ->method('findOneByField')
      ->will($this->returnCallback(
        function ($field, $value, $table) {
          return $this->findOneByFieldCallback(
            array(
              'sessions' => new TableDefinition('id', 111445, self::$SESSION_111445),
              'users' => new TableDefinition('id', 12, self::$USER_12),
              'modelbs' => new TableDefinition('id', 4, self::$MODELB_1)
            ), $field, $value, $table);
        }
      ));

    $this->app->call();

    $this->assertEquals(401, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $this->assertEquals(1007, json_decode($this->app->getResponse()->body())->error->code);
  }

  public function testWrongOwnerCollectionCall() {
    $middleware = new RESTOwnerOnlyAccess();

    $this->setUpApp(array('PATH_INFO' => '/modelbs', 'SESSION' => '111445'),
      array(new SessionHeader(), $middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('findOneByField')
      ->will($this->returnCallback(
        function ($field, $value, $table) {
          return $this->findOneByFieldCallback(
            array(
              'sessions' => new TableDefinition('id', 111445, self::$SESSION_111445),
              'users' => new TableDefinition('id', 12, self::$USER_12)
            ), $field, $value, $table);
        }
      ));

    $this->app->call();

    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
  }

  public function testDocs() {
    $middleware = new RESTOwnerOnlyAccess();
    $sessHeaderMiddleware = new SessionHeader();

    $this->setUpApp(array('PATH_INFO' => '/modelbs/4', 'SESSION' => '111444'),
      array($sessHeaderMiddleware, $middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->app->getDocJSON(new MarkdownParser());

    $docArray = array();
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $rRefMock = new \ReflectionMethod('\mabiTesting\ModelBController', '_restPostCollection');

    $sessHeaderMiddleware->documentMethod($rClassMock, $rRefMock, $docArray);
    $middleware->documentMethod($rClassMock, $rRefMock, $docArray);

    $this->assertInternalType('array', $docArray);
    $this->assertNotEmpty('array', $docArray['parameters']);
    $this->assertNotCount(0, $docArray['parameters']);

    $sessionFound = FALSE;
    foreach ($docArray['parameters'] as $parameterDoc) {
      if (is_array($parameterDoc) && $parameterDoc['Name'] == 'SESSION' && $parameterDoc['Location'] == 'header') {
        $this->assertEquals('N', $parameterDoc['Required']);
        $sessionFound = TRUE;
        break;
      }
    }

    $this->assertTrue($sessionFound);
  }
}