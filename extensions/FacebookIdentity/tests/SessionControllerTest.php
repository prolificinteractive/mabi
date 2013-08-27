<?php

namespace MABI\FacebookIdentity\Testing;

include_once __DIR__ . '/../FacebookIdentity.php';
include_once __DIR__ . '/../../Identity/Identity.php';
include_once __DIR__ . '/../../../tests/AppTestCase.php';

use MABI\FacebookIdentity\FacebookIdentity;
use MABI\Identity\Identity;
use MABI\RESTAccess\RESTAccess;
use MABI\Testing\AppTestCase;

class SessionControllerTest extends AppTestCase {
  /**
   * @var FacebookIdentity
   */
  protected $fbIdentityExtension;

  public function setUpApp($env = array(), $fbId, $fbEmail, $facebookOnly = FALSE) {
    parent::setUpApp($env);

    $fbData = new \stdClass();
    $fbData->id = $fbId;
    $fbData->email = $fbEmail;
    $fbData->first_name = 'photis';
    $fbData->last_name = 'patriotis';

    $this->app->setConfig('FacebookSessionMockData', $fbData);
    $this->fbIdentityExtension = new FacebookIdentity($this->app, new Identity($this->app, new RESTAccess($this->app)),
      $facebookOnly);
    $this->app->addExtension($this->fbIdentityExtension);
  }

  public function testSuccessfulSessionPostCollection() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"accessToken":"abcdfacebooktesttokenefgh"}',
      'PATH_INFO' => '/sessions'
    ), 1233344556, 'ppatriotis@gmail.com');

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
        'userId' => '2',
      )));

    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $output = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($output);
    $this->assertEquals('4', $output->sessionId);
    $this->assertEquals('2', $output->userId);
    $this->assertEquals('photis', $output->user->firstName);
    $this->assertEquals('1233344556', $output->user->facebookId);
  }

  public function testSuccessfulSessionPostCreateUserCollection() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"accessToken":"abcdfacebooktesttokenefgh"}',
      'PATH_INFO' => '/sessions'
    ), 1233344557, 'ppatriotis2@gmail.com');

    $this->dataConnectionMock->expects($this->exactly(3))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'myFindOneByFieldCallback')));

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('insert')
      ->will($this->returnCallback(array($this, 'myInsertCallback')));

    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $output = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($output);
    $this->assertEquals('4', $output->sessionId);
    $this->assertEquals('3', $output->userId);
    $this->assertEquals('photis', $output->user->firstName);
    $this->assertEquals('ppatriotis2@gmail.com', $output->user->email);
    $this->assertEquals('1233344557', $output->user->facebookId);
  }

  public function testSessionDocumentation() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"accessToken":"abcdfacebooktesttokenefgh"}',
      'PATH_INFO' => '/sessions'
    ), 1233344556, 'ppatriotis@gmail.com');

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
        'userId' => '2',
      )));

    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $output = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($output);
    $this->assertEquals('4', $output->sessionId);
    $this->assertEquals('2', $output->userId);
    $this->assertEquals('photis', $output->user->firstName);
    $this->assertEquals('1233344556', $output->user->facebookId);
  }

  public function myInsertCallback($table, $data) {
    $this->assertThat($table, $this->logicalOr($this->equalTo('sessions'), $this->equalTo('users')));

    switch ($table) {
      case 'sessions':
        return array(
          'id' => '4',
          'date_created' => time(),
          'lastAccessed' => time(),
          'userId' => $data['userId'],
        );
      case 'users':
        return array(
          'id' => 3,
          'created' => 1372375585,
          'firstName' => 'photis',
          'lastName' => 'patriotis',
          'email' => 'ppatriotis2@gmail.com',
          'passHash' => '604cefb585491865043db59f5f200c08af016dc636bcb37c858199e20f082c10',
          'facebookId' => '1233344557',
          // result of: hash_hmac('sha256', '123', 'salt4456');
          'salt' => 'salt4456'
        );
      default:
        return FALSE;
    }
  }

  public function myFindOneByFieldCallback($field, $value, $table) {
    $this->assertThat($table, $this->logicalOr($this->equalTo('sessions'), $this->equalTo('users')));

    switch ($table) {
      case 'sessions':
        $this->assertEquals(0, $value);
        $this->assertEquals('id', $field);
        return FALSE;
      case 'users':
        if ($field == 'facebookId' && $value == '1233344556') {
          return array(
            'id' => 2,
            'created' => 1372375585,
            'firstName' => 'photis',
            'lastName' => 'patriotis',
            'email' => 'ppatriotis@gmail.com',
            'passHash' => '604cefb585491865043db59f5f200c08af016dc636bcb37c858199e20f082c10',
            'facebookId' => '1233344556',
            // result of: hash_hmac('sha256', '123', 'salt4456');
            'salt' => 'salt4456'
          );
        }
      default:
        return FALSE;
    }
  }
}