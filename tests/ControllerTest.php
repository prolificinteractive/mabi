<?php

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/../DirectoryControllerLoader.php';
include_once __DIR__ . '/../GeneratedRESTModelControllerLoader.php';
include_once __DIR__ . '/../MongoDataConnection.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';

class ControllerTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var \MABI\App
   */
  protected $app;

  public function setUp() {
    \Slim\Environment::mock();
    $this->app = new \MABI\App();
    $this->app->addDataConnection('default', \MABI\MongoDataConnection::create('localhost', '27017', 'mabiTest'));
    $this->app->setModelLoaders(new \MABI\DirectoryModelLoader('TestModelDir', 'mabiTesting'));
  }

  public function testDirectoryControllerLoader() {
    $controllerLoader = new \MABI\DirectoryControllerLoader('TestControllerDir', $this->app, 'mabiTesting');
    $controllers = $controllerLoader->getControllers();
    $this->assertContains('mabiTesting\ModelB', $controllerLoader->getOverriddenModelClasses());
    $this->assertNotEmpty($controllers);
    $this->assertInstanceOf('\mabiTesting\ModelBController', $controllers[0]);
  }

  public function testGeneratedRESTModelControllerLoader() {
    $controllerLoader = new \MABI\GeneratedRESTModelControllerLoader(array('\mabiTesting\ModelA'), $this->app);
    $controllers = $controllerLoader->getControllers();
    $this->assertNotEmpty($controllers);
    $this->assertInstanceOf('\MABI\RESTModelController', $controllers[0]);
  }

  public function testControllerLoaders() {
    $this->app->setModelLoaders(array(new \MABI\DirectoryModelLoader('TestModelDir', 'mabiTesting')));

    $dirControllerLoader = new \MABI\DirectoryControllerLoader('TestControllerDir', $this->app, 'mabiTesting');
    $genControllerLoader = new \MABI\GeneratedRESTModelControllerLoader(
      array_diff($this->app->getModelClasses(), $dirControllerLoader->getOverriddenModelClasses()), $this->app);

    $this->assertCount(1,$dirControllerLoader->getControllers());
    $this->assertCount(1,$genControllerLoader->getControllers());
  }
}