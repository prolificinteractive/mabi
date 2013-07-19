<?php

namespace MABI\Identity\Testing;

include_once __DIR__ . '/../Identity.php';
include_once __DIR__ . '/../../../tests/MockDataConnection.php';

use MABI\Identity\Identity;
use MABI\RESTAccess\RESTAccess;

include_once 'PHPUnit/Autoload.php';

class SessionControllerTest extends \PHPUnit_Framework_TestCase {
  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $dataConnectionMock;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $controllerMock;

  /**
   * @var \MABI\App
   */
  protected $app;

  private function setUpRESTApp($env = array()) {
    \Slim\Environment::mock($env);
    $this->app = new \MABI\App();

    $this->dataConnectionMock = $this->getMock('\MABI\Testing\MockDataConnection',
      array('findOneByField', 'query', 'insert', 'save', 'deleteByField', 'clearAll', 'getNewId', 'findAll')
    );

    $this->app->addDataConnection('default', $this->dataConnectionMock);

    $this->app->addExtension(new Identity($this->app, new RESTAccess($this->app)));
  }

  public function testMissingPasswordPostCollection() {
    $this->setUpRESTApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"email":"ppatriotis@gmail.com","password":"1235"}',
      'PATH_INFO' => '/sessions'
    ));

    $this->app->call();
    $this->assertEquals(400, $this->app->getResponse()->status());
  }

  public function testInvalidPasswordPostCollection() {
    $this->setUpRESTApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"email":"ppatriotis@gmail.com"}',
      'PATH_INFO' => '/sessions'
    ));

    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    $this->app->call();
    $this->assertEquals(400, $this->app->getResponse()->status());
  }

  public function testSuccessfulPostCollection() {
    $this->setUpRESTApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"email":"ppatriotis@gmail.com","password":"123"}',
      'PATH_INFO' => '/sessions'
    ));

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    $this->dataConnectionMock->expects($this->once())
      ->method('insert')
      ->with('sessions', $this->anything())
      ->will($this->returnValue(array(
        'id' => '4',
        'date_created' => time(),
        'lastAccessed' => time(),
        'user' => '1',
      )));

    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $output = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($output);
    $this->assertEquals('4', $output->sessionId);
  }

  public function myFindOneByFieldCallback($field, $value, $table) {
    $this->assertThat($field, $this->logicalOr($this->equalTo('id'), $this->equalTo('email')));
    switch ($field) {
      case 'id':
        $this->assertEquals($value, 0);
        $this->assertEquals('sessions', $table);
        return FALSE;
        break;
      case 'email':
      default:
        $this->assertEquals('ppatriotis@gmail.com', $value);
        $this->assertEquals('users', $table);
        return array(
          'id' => 1,
          'created' => 1372375580,
          'firstName' => 'Photis',
          'lastName' => 'Patriotis',
          'email' => 'ppatriotis@gmail.com',
          'passHash' => '604cefb585491865043db59f5f200c08af016dc636bcb37c858199e20f082c10',
          // result of: hash_hmac('sha256', '123', 'salt4456');
          'salt' => 'salt4456'
        );
    }
  }
}