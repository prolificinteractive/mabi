<?php

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/../MongoDataConnection.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/../DirectoryControllerLoader.php';
include_once __DIR__ . '/../GeneratedRESTModelControllerLoader.php';

class AppTest extends PHPUnit_Framework_TestCase {

  public function setUp() {
    \Slim\Environment::mock();
  }

  public function testNew() {
    $app = new \MABI\App();
    $connection = \MABI\MongoDataConnection::create('localhost', '27017', 'foodTweeks');
    $app->addDataConnection('default', $connection);
    $this->assertNotEmpty($app->getDataConnection('default'));
    $this->assertInstanceOf('MABI\MongoDataConnection', $app->getDataConnection('default'));
  }

  public function testModelLoaders() {
    $app = new \MABI\App();
    $connection = \MABI\MongoDataConnection::create('localhost', '27017', 'foodTweeks');
    $app->addDataConnection('default', $connection);
    $app->setModelLoaders(array(new \MABI\DirectoryModelLoader('TestModelDir', 'mabiTesting')));

    $this->assertNotEmpty($app->getModelClasses());
    $this->assertContains('mabiTesting\ModelB', $app->getModelClasses());
  }

  public function testAllLoaders() {
    $app = new \MABI\App();
    $connection = \MABI\MongoDataConnection::create('localhost', '27017', 'foodTweeks');
    $app->addDataConnection('default', $connection);

    $app->setModelLoaders(array(new \MABI\DirectoryModelLoader('TestModelDir', 'mabiTesting')));

    $dirControllerLoader = new \MABI\DirectoryControllerLoader('TestControllerDir', $app, 'mabiTesting');
    $app->setControllerLoaders(array(
      $dirControllerLoader,
      new \MABI\GeneratedRESTModelControllerLoader(
        array_diff($app->getModelClasses(), $dirControllerLoader->getOverriddenModelClasses()), $app)
    ));
  }
}