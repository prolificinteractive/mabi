<?php

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../../middleware/AnonymousIdentifier.php';
include_once __DIR__ . '/../../DirectoryControllerLoader.php';

class AnonymousIdentifierTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var \MABI\App
   */
  protected $app;

  /**
   * @var \MABI\Controller
   */
  protected $controller;

  public function setUpApp($env = array()) {
    \Slim\Environment::mock($env);
    $this->app = new \MABI\App();

    $dirControllerLoader = new \MABI\DirectoryControllerLoader(__DIR__ . '/../TestApp/TestControllerDir', $this->app,
      'mabiTesting');
    foreach ($dirControllerLoader->getControllers() as $controller) {
      if (get_class($controller) == 'mabiTesting\JustAController') {
        $this->controller = $controller;
      }
    }
    $this->app->setControllerLoaders(array($dirControllerLoader));
  }

  public function testCall() {
    $this->setUpApp(array('ANONUUID' => 'test1', 'PATH_INFO' => '/justa/testfunc'));

    $middleware = new \MABI\Middleware\AnonymousIdentifier();
    $this->controller->addMiddleware($middleware);
    $this->app->getSlim()->call();

    $this->assertEquals('test1', $this->app->getSlim()->request()->anonymousId);
  }

  public function testDocs() {
    $this->setUpApp(array('ANONUUID' => 'test1', 'PATH_INFO' => '/justa/testfunc'));

    $middleware = new \MABI\Middleware\AnonymousIdentifier();
    $docArray = array();
    $rClassMock = $this->getMock('\ReflectionClass',array(),array(),'',false);
    $rRefMock = $this->getMock('\ReflectionMethod',array(),array(),'',false);
    $middleware->documentMethod($rClassMock, $rRefMock, $docArray);
    $this->assertNotEmpty($docArray);
  }
}