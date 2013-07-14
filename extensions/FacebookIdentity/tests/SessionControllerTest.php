<?php

namespace MABI\FacebookIdentity\Testing;

include_once __DIR__ . '/../FacebookIdentity.php';
include_once __DIR__ . '/../../Identity/Identity.php';

use MABI\FacebookIdentity\FacebookIdentity;
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

    $this->dataConnectionMock = $this->getMock('\MABI\DataConnection');
    $this->dataConnectionMock
      ->expects($this->any())
      ->method('getDefaultIdColumn')
      ->will($this->returnValue('id'));

    $this->app->addDataConnection('default', $this->dataConnectionMock);

    $mockFBIdentity = $this->getMock('MABI\FacebookIdentity\FacebookIdentity',
      array('getFBInfo'),
      array($this->app, new Identity($this->app, new RESTAccess($this->app)))
    );

    $mockFBIdentity->expects($this->once())
      ->method('getFBInfo')
      ->with('abcdfacebooktesttokenefgh')
      ->will($this->returnValue(
        json_decode(json_encode(array(
          'email' => 'ppatriotis+fbtest@ex.com',
          'id' => '12345',
        )))
      ));

    $this->app->addExtension($mockFBIdentity);
  }

  public function testSuccessfulSessionPostCollection() {
    $this->setUpRESTApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => 'accessToken=abcdfacebooktesttokenefgh',
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
    $this->assertEquals(200, $this->app->getSlim()->response()->status());
    $this->assertNotEmpty($this->app->getSlim()->response()->body());
    $output = json_decode($this->app->getSlim()->response()->body());
    $this->assertNotEmpty($output);
    $this->assertEquals('4', $output->id);
  }

  public function myFindOneByFieldCallback($field, $value, $table) {
    $this->assertThat($table, $this->logicalOr($this->equalTo('sessions'), $this->equalTo('users')));
    switch ($table) {
      case 'sessions':
        $this->assertNull($value);
        $this->assertEquals('id', $field);
        return FALSE;
      case 'users':
      default:
        return array(
          'id' => '2',
          'created' => 1372375585,
          'firstName' => 'photis',
          'lastName' => 'patriotis2',
          'email' => 'ppatriotis2@gmail.com',
          'passHash' => '604cefb585491865043db59f5f200c08af016dc636bcb37c858199e20f082c10',
          'facebookId' => '12345',
          // result of: hash_hmac('sha256', '123', 'salt4456');
          'salt' => 'salt4456'
        );
    }
  }
}