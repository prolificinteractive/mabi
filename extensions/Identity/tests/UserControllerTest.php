<?php

namespace MABI\Identity\Testing;

include_once __DIR__ . '/../Identity.php';
include_once __DIR__ . '/../../../tests/AppTestCase.php';
include_once __DIR__ . '/../../EmailSupport/Mandrill.php';
include_once __DIR__ . '/../../EmailSupport/TokenTemplate.php';

use MABI\App;
use MABI\Identity\Identity;
use MABI\RESTAccess\RESTAccess;
use MABI\Testing\AppTestCase;
use MABI\Testing\TableDefinition;

class UserControllerTest extends AppTestCase {
  /**
   * @var Identity
   */
  protected $identityExtension;

  protected static $USER_122 = array(
    'id' => 122,
    'created' => 1372375580,
    'firstName' => 'Photis',
    'lastName' => 'Patriotis',
    'email' => 'ppatriotis@gmail.com',
    'passHash' => '604cefb585491865043db59f5f200c08af016dc636bcb37c858199e20f082c10',
    // result of: hash_hmac('sha256', '123', 'salt4456');
    'salt' => 'salt4456',
    'lastAccessed' => 1379430989
  );

  protected static $SESSION_111444 = array(
    'created' => '1370663864',
    'userId' => 122
  );

  public function setUpApp($env = array(), $withCache = false) {
    parent::setUpApp($env, $withCache);
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
      ->will($this->returnCallback(array($this, 'findOneByFieldCreateUserCallback')));

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
      ->will($this->returnCallback(array($this, 'findOneByFieldCreateUserCallback')));

    // There are two insert calls, one for creating creating the session, and one for creating the user.
    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('insert')
      ->will($this->returnCallback(array($this, 'insertCallback')));

    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertNotEmpty($this->app->getResponse()->body());
    $output = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($output);
    $this->assertEquals('2', $output->userId);
    $this->assertEquals('4', $output->newSessionId);
  }

  public function findOneByFieldUpdateUserCallback($field, $value, $table, $fields,
                                                   $testEmailChange = TRUE, $testEmailExists = FALSE) {
    switch ($table) {
      case 'users':
        switch ($field) {
          case 'id':
            return $this->returnTableValue($field, $value, new TableDefinition('id', 122, self::$USER_122));
          case 'email':
            if ($testEmailChange) {
              $this->assertEquals($value, 'ppatriotis2@gmail.com');
              return FALSE;
            }
            elseif ($testEmailExists) {
              $this->assertEquals($value, 'ppatriotis+exists@gmail.com');
              return self::$USER_122;
            }
        }
        $this->fail("Invalid user field value: $field (value: $value)");
        return NULL;
      case 'sessions':
        return $this->returnTableValue($field, $value, new TableDefinition('id', 111444, self::$SESSION_111444));
    }
    $this->fail("Table '$table' should not be called");
    return NULL;
  }

  public function testSuccessfulUpdateResource() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'PUT',
      'slim.input' => '{"firstName":"photis","lastName":"patriotis2","email":"ppatriotis2@gmail.com","password":"777777"}',
      'SESSION' => '111444',
      'PATH_INFO' => '/users/122'
    ));

    $this->dataConnectionMock->expects($this->exactly(4))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'findOneByFieldUpdateUserCallback')));

    $this->dataConnectionMock->expects($this->once())
      ->method('findAllByField')
      ->with('userId', 122, 'sessions', array())
      ->will($this->returnValue(array(self::$SESSION_111444, array('id' => 111445, 'userId' => 122))));

    $this->dataConnectionMock->expects($this->once())
      ->method('deleteByField')
      ->with('id', 111445, 'sessions');

    $userDateUpdate = self::$USER_122;
    $now = new \DateTime('now');
    $userDateUpdate['lastAccessed'] = $now->getTimestamp();
    $userOtherUpdate = self::$USER_122;
    $userOtherUpdate['firstName'] = 'photis';
    $userOtherUpdate['lastName'] = 'patriotis2';
    $userOtherUpdate['email'] = 'ppatriotis2@gmail.com';
    $userOtherUpdate['passHash'] = 'facf86a33affc4c13a720ebfc8f5030faec8cedf2c1aa8855185e7d8cc0dab0b';
    // result of: hash_hmac('sha256', '777777', 'salt4456');

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('save')
      ->will($this->returnCallback(function ($table, $data, $field, $value) use ($userDateUpdate, $userOtherUpdate) {
        $this->assertEquals($table, 'users');
        $this->assertEquals($field, 'id');
        $this->assertEquals($value, 122);
        $this->assertThat($data, $this->logicalOr(
          $this->equalTo($userDateUpdate),
          $this->equalTo($userOtherUpdate)
        ));
      }));

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

    $this->dataConnectionMock->expects($this->exactly(3))
      ->method('findOneByField')
      ->will($this->returnCallback(function ($field, $value, $table, $fields) {
        return $this->findOneByFieldUpdateUserCallback($field, $value, $table, $fields, FALSE);
      }));

    $userDateUpdate = self::$USER_122;
    $now = new \DateTime('now');
    $userDateUpdate['lastAccessed'] = $now->getTimestamp();
    $userOtherUpdate = self::$USER_122;
    $userOtherUpdate['firstName'] = 'photis';
    $userOtherUpdate['lastName'] = 'patriotis2';

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('save')
      ->will($this->returnCallback(function ($table, $data, $field, $value) use ($userDateUpdate, $userOtherUpdate) {
        $this->assertEquals($table, 'users');
        $this->assertEquals($field, 'id');
        $this->assertEquals($value, 122);
        $this->assertThat($data, $this->logicalOr(
          $this->equalTo($userDateUpdate),
          $this->equalTo($userOtherUpdate)
        ));
      }));

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

    $this->dataConnectionMock->expects($this->exactly(4))
      ->method('findOneByField')
      ->will($this->returnCallback(function ($field, $value, $table, $fields) {
        return $this->findOneByFieldUpdateUserCallback($field, $value, $table, $fields, FALSE, TRUE);
      }));

    $this->app->call();
    $this->assertEquals(409, $this->app->getResponse()->status());
  }

  public function testForgotPasswordNoProvider() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"email":"ppatriotis@gmail.com"}',
      'PATH_INFO' => '/users/forgotpassword'
    ));

    $this->app->call();
    $this->assertEquals(500, $this->app->getResponse()->status());
  }

  public function testForgotPasswordNoTemplate() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"email":"ppatriotis@gmail.com"}',
      'PATH_INFO' => '/users/forgotpassword'
    ));

    $controllers = $this->app->getControllers();

    foreach ($controllers as $controller) {
      if (get_class($controller) == "MABI\\Identity\\UserController") {
        $controller->setEmailProvider(new \MABI\EmailSupport\Mandrill('12445', 'DEFAULT_SENDER', 'DEFAULT_NAME'));
      }
    }

    $this->app->call();
    $this->assertEquals(500, $this->app->getResponse()->status());
  }

  public function testForgotPasswordNoEmail() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"Noemail":"triotis@gmail.com"}',
      'PATH_INFO' => '/users/forgotpassword'
    ));

    $controllers = $this->app->getControllers();

    foreach ($controllers as $controller) {
      if (get_class($controller) == "MABI\\Identity\\UserController") {
        $controller->setEmailProvider(new \MABI\EmailSupport\Mandrill('12445', 'DEFAULT_SENDER', 'DEFAULT_NAME'));
        $controller->setForgotEmailTemplate(new \MABI\EmailSupport\TokenTemplate('TestTemplate', 'Password Reset'));
      }
    }

    $this->app->call();
    $this->assertEquals(400, $this->app->getResponse()->status());
  }

  public function testForgotPasswordBadEmail() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"email":"ppatriotis2@gmail.com"}',
      'PATH_INFO' => '/users/forgotpassword'
    ));

    $controllers = $this->app->getControllers();

    foreach ($controllers as $controller) {
      if (get_class($controller) == "MABI\\Identity\\UserController") {
        $controller->setEmailProvider(new \MABI\EmailSupport\Mandrill('12445', 'DEFAULT_SENDER', 'DEFAULT_NAME'));
        $controller->setForgotEmailTemplate(new \MABI\EmailSupport\TokenTemplate('TestTemplate', 'Password Reset'));
      }
    }

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'findOneByFieldForgotPasswordCallback')));

    $this->app->call();
    $this->assertEquals(400, $this->app->getResponse()->status());
  }

  public function testForgotPassword() {
    $this->setUpApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"email":"ppatriotis@gmail.com"}',
      'PATH_INFO' => '/users/forgotpassword'
    ));

    $controllers = $this->app->getControllers();

    $mandrillMock = $this->getMock('\MABI\EmailSupport\Mandrill', array(), array(
      '12445',
      'DEFAULT_SENDER',
      'DEFAULT_NAME'
    ));
    $mandrillMock->expects($this->once())
      ->method('sendEmail')
      ->will($this->returnValue(json_encode(array(
        "email" => "conord33@gmail.com",
        "status" => "sent",
        "_id" => "4b35185b2ca04ffaacbd1abf2e32dabc",
        "reject_reason" => NULL
      ))));

    foreach ($controllers as $controller) {
      if (get_class($controller) == "MABI\\Identity\\UserController") {
        $controller->setEmailProvider($mandrillMock);
        $controller->setForgotEmailTemplate(new \MABI\EmailSupport\TokenTemplate('TestTemplate', 'Password Reset'));
      }
    }

    $this->dataConnectionMock->expects($this->exactly(2))
      ->method('findOneByField')
      ->will($this->returnCallback(array($this, 'findOneByFieldForgotPasswordCallback')));

    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
  }

  public function findOneByFieldForgotPasswordCallback($field, $value, $table) {
    $this->assertThat($field, $this->logicalOr($this->equalTo('id'), $this->equalTo('email')));

    $userVal = self::$USER_122;

    switch ($field) {
      case 'id':
        $this->assertThat($table, $this->logicalOr($this->equalTo('sessions'), $this->equalTo('users')));
        if ($table == 'sessions') {
          return FALSE;
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
          default:
            $this->fail('Invalid value: ' . $value);
            return FALSE;
        }
    }
  }

  public function findOneByFieldCreateUserCallback($field, $value, $table) {
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
          return self::$USER_122;
        }
        return FALSE;
    }
  }

  public function insertCallback($table, $value) {
    switch ($table) {
      case 'sessions':
        $this->assertEquals($value['userId'], '2');
        return array(
          'id' => 4,
          'created' => time(),
          'lastAccessed' => time(),
          'userId' => '2',
        );
      case 'users':
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
    $this->fail("Table '$table' should not be called");
    return NULL;
  }

}
