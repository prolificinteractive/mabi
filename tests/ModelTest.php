<?php

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/../Model.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/../MongoDataConnection.php';

class ModelTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var \MABI\App
   */
  protected $app;

  public function setUp() {
    \Slim\Environment::mock();
    $this->app = new \MABI\App();
    $this->app->addDataConnection('default', \MABI\MongoDataConnection::create('localhost', '27017', 'test'));
  }

  public function testModelLoader() {
    $modelLoader = new \MABI\DirectoryModelLoader('TestModelDir', 'mabiTesting');
    $models = $modelLoader->loadModels();
    $this->assertContains('\mabiTesting\ModelA', $models);
    $this->assertContains('\mabiTesting\ModelB', $models);
  }

  public function testInit() {
    $amodel = \mabiTesting\ModelA::init($this->app);
    $this->assertNotEmpty($amodel);
  }

  public function testFindById() {
    /**
     * @var $amodel \mabiTesting\ModelA
     */
    $amodel = \mabiTesting\ModelA::init($this->app);
    $amodel->findById(new \MongoId('5127ef4f20cb24af1c4557de'));
    $this->assertNotEmpty($amodel);
    $this->assertNotEmpty($amodel->partner);
    $this->assertNotEmpty($amodel->partner->name);
  }
}