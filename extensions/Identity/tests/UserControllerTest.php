<?php

namespace MABI\Identity\Testing;

include_once __DIR__ . '/../Identity.php';

use MABI\Identity\Identity;
use MABI\RESTAccess\RESTAccess;

include_once 'PHPUnit/Autoload.php';

class UserControllerTest extends \PHPUnit_Framework_TestCase {
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

    $this->dataConnectionMock = $this->getMock('\MABI\DataConnection');
    $this->dataConnectionMock
      ->expects($this->any())
      ->method('getDefaultIdColumn')
      ->will($this->returnValue('id'));

    $this->app->addDataConnection('default', $this->dataConnectionMock);

    $this->app->addExtension(new Identity($this->app, new RESTAccess($this->app)));
  }

  public function testNoPasswordPostCollection() {
    $this->setUpRESTApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => 'firstName=photis&lastName=patriotis&email=ppatriotis@gmail.com',
      'PATH_INFO' => '/users'
    ));

    $this->app->call();
    $this->assertEquals(400, $this->app->getResponse()->status());
  }

  public function testShortPasswordPostCollection() {
    $this->setUpRESTApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => 'firstName=photis&lastName=patriotis&email=ppatriotis@gmail.com&password=123',
      'PATH_INFO' => '/users'
    ));

    $this->app->call();
    $this->assertEquals(400, $this->app->getResponse()->status());
  }

  public function testMissingEmailPostCollection() {
    $this->setUpRESTApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => 'firstName=photis&lastName=patriotis&password=123456',
      'PATH_INFO' => '/users'
    ));

    $this->app->call();
    $this->assertEquals(400, $this->app->getResponse()->status());
  }

  public function testExistingEmailPostCollection() {
    $this->setUpRESTApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => 'firstName=photis&lastName=patriotis&email=ppatriotis@gmail.com&password=123456',
      'PATH_INFO' => '/users'
    ));

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    $this->app->call();
    $this->assertEquals(409, $this->app->getResponse()->status());
  }

  public function testSuccessfulPostCollection() {
    $this->setUpRESTApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => 'firstName=photis&lastName=patriotis2&email=ppatriotis2@gmail.com&password=123456',
      'PATH_INFO' => '/users'
    ));

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    // There are two insert calls, one for creating creating the session, and one for creating the user.
    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('insert')
      ->will($this->returnCallback(array($this, 'myInsertCallback')));

    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $output = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($output);
    $this->assertEquals('2', $output->userId);
    $this->assertEquals('4', $output->newSessionId);
  }

  public function myFindOneByFieldCallback($field, $value, $table) {
    $this->assertThat($field, $this->logicalOr($this->equalTo('id'), $this->equalTo('email')));
    switch ($field) {
      case 'id':
        $this->assertNull($value);
        $this->assertEquals('sessions', $table);
        return FALSE;
        break;
      case 'email':
      default:
        $this->assertEquals('users', $table);
        $this->assertThat($value, $this->logicalOr($this->equalTo('ppatriotis@gmail.com'),
          $this->equalTo('ppatriotis2@gmail.com')));
        if ($value == 'ppatriotis@gmail.com') {
          return array(
            'id' => '1',
            'created' => 1372375580,
            'firstName' => 'Photis',
            'lastName' => 'Patriotis',
            'email' => 'ppatriotis@gmail.com',
            'passHash' => '604cefb585491865043db59f5f200c08af016dc636bcb37c858199e20f082c10',
            // result of: hash_hmac('sha256', '123', 'salt4456');
            'salt' => 'salt4456'
          );
        }
        return FALSE;
    }
  }

  public function myInsertCallback($table, $value) {
    $this->assertThat($table, $this->logicalOr($this->equalTo('sessions'), $this->equalTo('users')));
    switch ($table) {
      case 'sessions':
        $this->assertEquals($value['userId'], '2');
        return array(
          'id' => '4',
          'date_created' => time(),
          'lastAccessed' => time(),
          'userId' => '2',
        );
        break;
      case 'users':
      default:
        return array(
          'id' => '2',
          'created' => 1372375585,
          'firstName' => 'photis',
          'lastName' => 'patriotis2',
          'email' => 'ppatriotis2@gmail.com',
          'passHash' => '604cefb585491865043db59f5f200c08af016dc636bcb37c858199e20f082c10',
          // result of: hash_hmac('sha256', '123', 'salt4456');
          'salt' => 'salt4456'
        );
    }
  }

}