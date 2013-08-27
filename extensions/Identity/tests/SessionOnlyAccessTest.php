<?php

namespace MABI\Identity\Testing;

use MABI\Autodocs\MarkdownParser;
use MABI\Identity\Identity;
use MABI\Identity\Middleware\SessionHeader;
use MABI\Identity\Middleware\SessionOnlyAccess;
use MABI\RESTAccess\RESTAccess;
use MABI\Testing\MiddlewareTestCase;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../../../tests/middleware/MiddlewareTestCase.php';
include_once __DIR__ . '/../../../autodocs/MarkdownParser.php';
include_once __DIR__ . '/../Identity.php';
include_once __DIR__ . '/../middleware/SessionHeader.php';
include_once __DIR__ . '/../middleware/SessionOnlyAccess.php';

class SessionOnlyAccessTest extends MiddlewareTestCase {

  public function testSuccessfulCall() {
    $middleware = new SessionOnlyAccess();

    $this->setUpApp(array('PATH_INFO' => '/modelbs/4', 'SESSION' => '111444'),
      array(new SessionHeader(), $middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->any())
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    $this->app->call();

    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $output = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($output);
    $this->assertEquals('test', $output->name);
  }

  public function testNoSessionCall() {
    $middleware = new SessionOnlyAccess();

    $this->setUpApp(array('PATH_INFO' => '/modelbs/4'),
      array($middleware));
    $identity = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identity);

    $this->dataConnectionMock->expects($this->any())
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    $this->app->call();

    $this->assertEquals(401, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $this->assertEquals(1007, json_decode($this->app->getResponse()->body())->error->code);
  }

  public function myFindOneByFieldCallback($field, $value, $table) {
    $this->assertThat($table, $this->logicalOr($this->equalTo('sessions'), $this->equalTo('modelbs')));
    $this->assertEquals('id', $field);

    switch ($table) {
      case 'sessions':
        $this->assertThat($value, $this->logicalOr($this->equalTo(111444),
          $this->equalTo(111445)));

        if ($value == 111444) {
          return array(
            'created' => '1370663864',
            'userId' => 11
          );
        }
        return array(
          'created' => '1370663865',
          'userId' => 12
        );
      case 'modelbs':
      default:
        $this->assertEquals('4', $value);
        return array('modelBId' => 1, 'name' => 'test', 'testOwner' => 11);
    }
  }

  public function testDocs() {
    $middleware = new SessionOnlyAccess();
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
        $this->assertEquals('Y', $parameterDoc['Required']);
        $sessionFound = TRUE;
        break;
      }
    }

    $this->assertTrue($sessionFound);
  }
}