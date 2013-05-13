<?php

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../../middleware/APIApplicationOnlyAccess.php';
include_once __DIR__ . '/../../middleware/SharedSecret.php';
include_once __DIR__ . '/../../DirectoryControllerLoader.php';

class APIApplicationOnlyAccessTest extends \PHPUnit_Framework_TestCase {

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

  public function setUpApp($env = array(), $middlewares = array()) {
    \Slim\Environment::mock($env);
    $this->app = new \MABI\App();

    $dirControllerLoader = new \MABI\DirectoryControllerLoader(__DIR__ . '/../TestApp/TestControllerDir', $this->app,
      'mabiTesting');
    foreach ($dirControllerLoader->getControllers() as $controller) {
      if (get_class($controller) == 'mabiTesting\JustAController') {
        $this->controller = $controller;
        if (!empty($middlewares)) {
          foreach ($middlewares as $middleware) {
            $this->controller->addMiddleware($middleware);
          }
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

  public function testStoppedCall() {
    $middleware = new \MABI\Middleware\APIApplicationOnlyAccess();
    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc'), array($middleware));

    $this->app->getSlim()->call();

    $this->assertEquals(401, $this->app->getSlim()->response()->status());
  }

  public function testPassedCall() {
    $middleware = new \MABI\Middleware\SharedSecret();
    $middleware2 = new \MABI\Middleware\APIApplicationOnlyAccess();

    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc', 'SHARED_SECRET' => 'TEST-SECRET-1'),
      array($middleware, $middleware2));

    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('sharedSecret', 'TEST-SECRET-1', 'applications', array())
      ->will($this->returnValue(array(
        'applicationName' => 'AppName',
        'sharedSecret' => 'TEST-SECRET-1'
      )));

    $this->app->getSlim()->call();

    $this->assertEquals(200, $this->app->getSlim()->response()->status());
  }

  public function testDocs() {
    $this->setUpApp(array('ANONUUID' => 'test1', 'PATH_INFO' => '/justa/testfunc'));

    $middleware = new \MABI\Middleware\AnonymousIdentifier();
  }
}