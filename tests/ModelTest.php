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

  /**
   * @var \mabiTesting\ModelA
   */
  protected $insertedModel;

  public function setUp() {
    \Slim\Environment::mock();
    $this->app = new \MABI\App();
    $this->app->addDataConnection('default', \MABI\MongoDataConnection::create('localhost', '27017', 'mabiTest'));
  }

  public function testModelLoader() {
    $modelLoader = new \MABI\DirectoryModelLoader('TestModelDir', 'mabiTesting');
    $models = $modelLoader->loadModels();
    $this->assertContains('mabiTesting\ModelA', $models);
    $this->assertContains('mabiTesting\ModelB', $models);
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
    $amodel->findById('5135256d84300a70ee8d08e2');
    $this->assertNotEmpty($amodel);
    $this->assertNotEmpty($amodel->partner);
    $this->assertNotEmpty($amodel->partner->name);
  }

  public function testInsertAndDelete() {
    /**
     * @var $amodel \mabiTesting\ModelA
     */
    $amodel = \mabiTesting\ModelA::init($this->app);
    $amodel->init_id = '5';
    $bmodel = \mabiTesting\ModelB::init($this->app);
    $bmodel->findById('5135257f84300a70ee8d08e3');
    $amodel->partner = $bmodel;
    $amodel->insert();

    $this->assertInstanceOf('MongoId', $amodel->id);

    $oldId = $amodel->id;
    $amodel->delete();

    $amodel = \mabiTesting\ModelA::init($this->app);
    $result = $amodel->findById($oldId);

    $this->assertFalse($result);
  }
}