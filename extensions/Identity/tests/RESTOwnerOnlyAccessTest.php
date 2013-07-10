<?php

namespace MABI\Identity\Testing;

use MABI\Autodocs\MarkdownParser;
use MABI\Identity\Identity;
use MABI\Identity\Middleware\RESTOwnerOnlyAccess;
use MABI\Identity\Middleware\SessionHeader;
use MABI\RESTAccess\RESTAccess;
use MABI\Testing\MiddlewareTestCase;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../../../tests/middleware/MiddlewareTestCase.php';
include_once __DIR__ . '/../../../autodocs/MarkdownParser.php';
include_once __DIR__ . '/../Identity.php';
include_once __DIR__ . '/../../RESTAccess/RESTAccess.php';
include_once __DIR__ . '/../middleware/RESTOwnerOnlyAccess.php';
include_once __DIR__ . '/../middleware/SessionHeader.php';

class RESTOwnerOnlyAccessTest extends MiddlewareTestCase {

  public function testSuccessfulCall() {
    $middleware = new RESTOwnerOnlyAccess();

    $this->setUpRESTApp(array('PATH_INFO' => '/modelbs/4', 'SESSION' => 'TEST-SESSION-ID-1'),
      array(new SessionHeader(), $middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->any())
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    $this->app->call();

    $this->assertEquals(200, $this->app->getSlim()->response()->status());
    $this->assertNotEmpty($this->app->getSlim()->response()->body());
    $output = json_decode($this->app->getSlim()->response()->body());
    $this->assertNotEmpty($output);
    $this->assertEquals('test', $output->name);
  }

  public function testNoSessionCall() {
    $middleware = new RESTOwnerOnlyAccess();

    $this->setUpRESTApp(array('PATH_INFO' => '/modelbs/4'),
      array($middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->any())
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    $this->app->call();

    $this->assertEquals(401, $this->app->getSlim()->response()->status());
    $this->assertEmpty($this->app->getSlim()->response()->body());
  }

  public function testWrongOwnerCall() {
    $middleware = new RESTOwnerOnlyAccess();

    $this->setUpRESTApp(array('PATH_INFO' => '/modelbs/4', 'SESSION' => 'TEST-SESSION-ID-2'),
      array(new SessionHeader(), $middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->any())
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    $this->app->call();

    $this->assertEquals(401, $this->app->getSlim()->response()->status());
    $this->assertEmpty($this->app->getSlim()->response()->body());
  }

  public function testWrongOwnerCollectionCall() {
    $middleware = new RESTOwnerOnlyAccess();

    $this->setUpRESTApp(array('PATH_INFO' => '/modelbs', 'SESSION' => 'TEST-SESSION-ID-2'),
      array(new SessionHeader(), $middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->any())
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    $this->app->call();

    $this->assertEquals(200, $this->app->getSlim()->response()->status());
    $this->assertNotEmpty($this->app->getSlim()->response()->body());
  }

  public function myFindOneByFieldCallback($field, $value, $table) {
    $this->assertThat($table, $this->logicalOr($this->equalTo('sessions'), $this->equalTo('modelbs')));
    $this->assertEquals('id', $field);

    switch ($table) {
      case 'sessions':
        $this->assertThat($value, $this->logicalOr($this->equalTo('TEST-SESSION-ID-1'),
          $this->equalTo('TEST-SESSION-ID-2')));

        if ($value == 'TEST-SESSION-ID-1') {
          return array(
            'created' => '1370663864',
            'user' => 'TEST-USER-ID-1'
          );
        }
        return array(
          'created' => '1370663865',
          'user' => 'TEST-USER-ID-2'
        );
      case 'modelbs':
      default:
        $this->assertEquals('4', $value);
        return array('modelBId' => 1, 'name' => 'test', 'testOwner' => 'TEST-USER-ID-1');
    }
  }


  public function testDocs() {
    $middleware = new RESTOwnerOnlyAccess();
    $sessHeaderMiddleware = new SessionHeader();

    $this->setUpRESTApp(array('PATH_INFO' => '/modelbs/4', 'SESSION' => 'TEST-SESSION-ID-1'),
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
        $this->assertEquals('Y', $parameterDoc['Required']);
        $sessionFound = TRUE;
        break;
      }
    }

    $this->assertTrue($sessionFound);
  }
}