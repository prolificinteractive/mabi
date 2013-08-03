<?php

namespace MABI\Identity\Testing;

include_once __DIR__ . '/../Identity.php';
include_once __DIR__ . '/../../../tests/AppTestCase.php';

use MABI\App;
use MABI\Identity\Identity;
use MABI\RESTAccess\RESTAccess;
use MABI\Testing\AppTestCase;

class UserControllerTest extends AppTestCase {
  /**
   * @var Identity
   */
  protected $identityExtension;

  public function setUpApp($env = array()) {
    parent::setUpApp($env);
    $identityExtension = new Identity($this->app, new RESTAccess($this->app));
    $this->app->addExtension($identityExtension);
  }

  public function testNoPasswordPostCollection() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"firstName":"photis","lastName":"patriotis","email":"ppatriotis@gmail.com"}',
      'PATH_INFO' => '/users'
    ));

    $this->app->call();
    $this->assertEquals(400, $this->app->getResponse()->status());
  }

  public function testShortPasswordPostCollection() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"firstName":"photis","lastName":"patriotis","email":"ppatriotis@gmail.com","password":"123"}',
      'PATH_INFO' => '/users'
    ));

    $this->app->call();
    $this->assertEquals(400, $this->app->getResponse()->status());
  }

  public function testMissingEmailPostCollection() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"firstName":"photis","lastName":"patriotis","password":"123456"}',
      'PATH_INFO' => '/users'
    ));

    $this->app->call();
    $this->assertEquals(400, $this->app->getResponse()->status());
  }

  public function testExistingEmailPostCollection() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"firstName":"photis","lastName":"patriotis","email":"ppatriotis@gmail.com","password":"123456"}',
      'PATH_INFO' => '/users'
    ));

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCreateUserCallback')));

    $this->app->call();
    $this->assertEquals(409, $this->app->getResponse()->status());
  }

  public function testSuccessfulPostCollection() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"firstName":"photis","lastName":"patriotis2","email":"ppatriotis2@gmail.com","password":"123456"}',
      'PATH_INFO' => '/users'
    ));

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCreateUserCallback')));

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

  public function setUpUpdateResourceTest($inputData) {

  }

  public function testSuccessfulUpdateResource() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'PUT',
      'slim.input' => '{"firstName":"photis","lastName":"patriotis2","email":"ppatriotis2@gmail.com","password":"777777"}',
      'SESSION' => '111444',
      'PATH_INFO' => '/users/122'
    ));

    $this->dataConnectionMock->expects($this->exactly(3))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldUpdateUserCallback')));

    $this->dataConnectionMock->expects($this->once())
      ->method('findAllByField')
      ->with('userId', 122, 'sessions', array())
      ->will($this->returnValue(
        array(
          array(
            'id' => 111444,
            'userId' => 122
          ),
          array(
            'id' => 111445,
            'userId' => 122
          ),
        )
      ));

    $this->dataConnectionMock->expects($this->once())
      ->method('deleteByField')
      ->with('id', 111445, 'sessions');

    $this->dataConnectionMock->expects($this->once())
      ->method('save')
      //$table, $data, $field, $value
      ->with('users', array(
        'id' => 122,
        'created' => 1372375580,
        'firstName' => 'photis',
        'lastName' => 'patriotis2',
        'email' => 'ppatriotis2@gmail.com',
        'passHash' => 'facf86a33affc4c13a720ebfc8f5030faec8cedf2c1aa8855185e7d8cc0dab0b',
        // result of: hash_hmac('sha256', '777777', 'salt4456');
        'salt' => 'salt4456'
      ), 'id', 122);

    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
  }

  public function testSuccessfulUpdateResourceNoPassword() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'PUT',
      'slim.input' => '{"firstName":"photis","lastName":"patriotis2","email":"ppatriotis@gmail.com"}',
      'SESSION' => '111444',
      'PATH_INFO' => '/users/122'
    ));

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldUpdateUserCallback')));

    $this->dataConnectionMock->expects($this->once())
      ->method('save')
      //$table, $data, $field, $value
      ->with('users', array(
        'id' => 122,
        'created' => 1372375580,
        'firstName' => 'photis',
        'lastName' => 'patriotis2',
        'email' => 'ppatriotis@gmail.com',
        'passHash' => '433813e38c7f564a06319c74c16d7e30f9cf645c0712a183ed0cbae3d74d24de',
        // result of: hash_hmac('sha256', '777777', 'salt4456');
        'salt' => 'salt4456'
      ), 'id', 122);

    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
  }

  public function testSuccessfulUpdateResourceExistingEmail() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'PUT',
      'slim.input' => '{"firstName":"photis","lastName":"patriotis2","email":"ppatriotis+exists@gmail.com"}',
      'SESSION' => '111444',
      'PATH_INFO' => '/users/122'
    ));

    $this->dataConnectionMock->expects($this->exactly(3))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldUpdateUserCallback')));

    $this->app->call();
    $this->assertEquals(409, $this->app->getResponse()->status());
  }

  public function myFindOneByFieldUpdateUserCallback($field, $value, $table) {
    $this->assertThat($field, $this->logicalOr($this->equalTo('id'), $this->equalTo('email')));

    $userVal = array(
      'id' => 122,
      'created' => 1372375580,
      'firstName' => 'Photis',
      'lastName' => 'Patriotis',
      'email' => 'ppatriotis@gmail.com',
      'passHash' => '433813e38c7f564a06319c74c16d7e30f9cf645c0712a183ed0cbae3d74d24de',
      // result of: hash_hmac('sha256', '123456', 'salt4456');
      'salt' => 'salt4456'
    );

    switch ($field) {
      case 'id':
        $this->assertThat($table, $this->logicalOr($this->equalTo('sessions'), $this->equalTo('users')));
        if ($table == 'sessions') {
          $this->assertEquals(111444, $value);
          return array(
            'id' => 111444,
            'userId' => 122
          );
        }
        $this->assertEquals(122, $value);
        return $userVal;
      case 'email':
      default:
        $this->assertEquals('users', $table);
        switch ($value) {
          case 'ppatriotis2@gmail.com':
            return FALSE;
          case 'ppatriotis@gmail.com':
            return $userVal;
          case 'ppatriotis+exists@gmail.com':
            $userVal['email'] = 'ppatriotis+exists@gmail.com';
            return $userVal;
          default:
            $this->fail('Invalid value: ' . $value);
            return FALSE;
        }
    }
  }

  public function myFindOneByFieldCreateUserCallback($field, $value, $table) {
    $this->assertThat($field, $this->logicalOr($this->equalTo('id'), $this->equalTo('email')));
    switch ($field) {
      case 'id':
        $this->assertEquals(0, $value);
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
            'passHash' => '433813e38c7f564a06319c74c16d7e30f9cf645c0712a183ed0cbae3d74d24de',
            // result of: hash_hmac('sha256', '123456', 'salt4456');
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
          'id' => 4,
          'date_created' => time(),
          'lastAccessed' => time(),
          'userId' => '2',
        );
        break;
      case 'users':
      default:
        return array(
          'id' => 2,
          'created' => 1372375585,
          'firstName' => 'photis',
          'lastName' => 'patriotis2',
          'email' => 'ppatriotis2@gmail.com',
          'passHash' => '433813e38c7f564a06319c74c16d7e30f9cf645c0712a183ed0cbae3d74d24de',
          // result of: hash_hmac('sha256', '123456', 'salt4456');
          'salt' => 'salt4456'
        );
    }
  }

}