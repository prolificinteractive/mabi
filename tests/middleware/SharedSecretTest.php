<?php

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../../middleware/SharedSecret.php';
include_once __DIR__ . '/../../DirectoryControllerLoader.php';

class SharedSecretTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var \MABI\App
   */
  protected $app;

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $dataConnectionMock;

  /**
   * @var \MABI\Controller
   */
  protected $controller;

  public function setUpApp($env = array(), $middleware = NULL) {
    \Slim\Environment::mock($env);
    $this->app = new \MABI\App();

    $dirControllerLoader = new \MABI\DirectoryControllerLoader(__DIR__ . '/../TestApp/TestControllerDir', $this->app,
      'mabiTesting');
    foreach ($dirControllerLoader->getControllers() as $controller) {
      if (get_class($controller) == 'mabiTesting\JustAController') {
        $this->controller = $controller;
        if (!empty($middleware)) {
          $this->controller->addMiddleware($middleware);
        }
      }
    }

    $this->dataConnectionMock = $this->getMock('\MABI\DataConnection');
    $this->dataConnectionMock
      ->expects($this->any())
      ->method('getDefaultIdColumn')
      ->will($this->returnValue('id'));

    $this->app->addDataConnection('default', $this->dataConnectionMock);

    $this->app->setControllerLoaders(array($dirControllerLoader));
  }

  public function testCall() {
    $middleware = new \MABI\Middleware\SharedSecret();

    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc', 'SHARED_SECRET' => 'TEST-SECRET-1'),
      $middleware);

    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('sharedSecret', 'TEST-SECRET-1', 'applications', array())
      ->will($this->returnValue(array(
        'applicationName' => 'AppName',
        'sharedSecret' => 'TEST-SECRET-1'
      )));

    $this->app->getSlim()->call();

    $this->assertEquals(200, $this->app->getSlim()->response()->status());
    $this->assertNotEmpty($this->app->getSlim()->request()->apiApplication);
    $this->assertInstanceOf('\MABI\DefaultApplicationModel', $this->app->getSlim()->request()->apiApplication);
    $this->assertEquals('AppName', $this->app->getSlim()->request()->apiApplication->applicationName);
  }

  // todo: test custom application class
  // todo: test custom share secret field

  public function testDocs() {
    $this->setUpApp(array('ANONUUID' => 'test1', 'PATH_INFO' => '/justa/testfunc'));

    $middleware = new \MABI\Middleware\SharedSecret();
    $docArray = array();
    $rClassMock = $this->getMock('\ReflectionClass', array(), array(), '', FALSE);
    $rRefMock = $this->getMock('\ReflectionMethod', array(), array(), '', FALSE);
    $middleware->documentMethod($rClassMock, $rRefMock, $docArray);
    $this->assertNotEmpty($docArray);
  }
}