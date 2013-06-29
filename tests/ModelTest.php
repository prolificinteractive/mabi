<?php

namespace MABI\Testing;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/../Model.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/../DataConnection.php';

class ModelTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var \MABI\App
   */
  protected $app;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $dataConnectionMock;

  /**
   * @var \mabiTesting\ModelA
   */
  protected $insertedModel;

  public function setUp() {
    \Slim\Environment::mock();
    $this->app = new \MABI\App();

    $this->dataConnectionMock = $this->getMock('\MABI\DataConnection');
    $this->dataConnectionMock
      ->expects($this->any())
      ->method('getDefaultIdColumn')
      ->will($this->returnValue('id'));

    $this->app->addDataConnection('default', $this->dataConnectionMock);

    $this->app->setModelLoaders(array(new \MABI\DirectoryModelLoader('TestApp/TestModelDir', 'mabiTesting')));
  }

  public function testModelLoader() {
    $modelLoader = new \MABI\DirectoryModelLoader('TestApp/TestModelDir', 'mabiTesting');
    $models = $modelLoader->loadModels();
    $this->assertContains('mabiTesting\ModelA', $models);
    $this->assertContains('mabiTesting\ModelB', $models);
  }

  public function testInit() {
    $amodel = \mabiTesting\ModelA::init($this->app);
    $this->assertNotEmpty($amodel);
  }

  public function testFindById() {
    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('id', 1, 'modelas')
      ->will($this->returnValue(array(
        'id' => 1,
        'init_id' => '2',
        'partner' => array('modelBId' => 1, 'name' => 'test')
      )));

    /**
     * @var $amodel \mabiTesting\ModelA
     */
    $amodel = \mabiTesting\ModelA::init($this->app);
    $amodel->findById(1);
    $this->assertNotEmpty($amodel);
    $this->assertNotEmpty($amodel->partner);
    $this->assertNotEmpty($amodel->partner->name);
  }

  public function testFindByField() {
    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('init_id', '2', 'modelas')
      ->will($this->returnValue(array(
        'id' => 1,
        'init_id' => '2',
        'partner' => array('modelBId' => 1, 'name' => 'test')
      )));

    /**
     * @var $amodel \mabiTesting\ModelA
     */
    $amodel = \mabiTesting\ModelA::init($this->app);
    $amodel->findByField('init_id', '2');
    $this->assertNotEmpty($amodel);
    $this->assertNotEmpty($amodel->partner);
    $this->assertNotEmpty($amodel->partner->name);
  }

  public function testQuery() {
    $this->dataConnectionMock->expects($this->once())
      ->method('query')
      ->with('modelas', array('testQuery' => 1))
      ->will($this->returnValue(array(
        array(
          'id' => 1,
          'init_id' => '2',
          'partner' => array('modelBId' => 1, 'name' => 'test')
        )
      )));

    /**
     * @var $amodel \mabiTesting\ModelA
     */
    $amodel = \mabiTesting\ModelA::init($this->app);
    $foundModels = $amodel->query(array('testQuery' => 1));
    $this->assertInternalType('array', $foundModels);
    $this->assertNotEmpty($foundModels);
    $this->assertNotEmpty($foundModels[0]);
    $this->assertNotEmpty($foundModels[0]->partner);
    $this->assertNotEmpty($foundModels[0]->partner->name);
  }

  // todo: test findAll

  public function testInsert() {
    $this->dataConnectionMock->expects($this->once())
      ->method('insert')
      ->with('modelas', array(
        'init_id' => '5',
        'partner' => array(
          'id' => 1,
          'name' => 'test',
          'testOwner' => NULL
        )
      ))
      ->will($this->returnValue(
        array(
          'id' => '10',
          'init_id' => '5',
          'partner' => array(
            'id' => 1,
            'name' => 'test'
          )
        )));
    /**
     * @var $amodel \mabiTesting\ModelA
     */
    $amodel = \mabiTesting\ModelA::init($this->app);
    $amodel->init_id = '5';
    $bmodel = \mabiTesting\ModelB::init($this->app);
    $bmodel->modelBId = 1;
    $bmodel->name = 'test';
    $amodel->partner = $bmodel;
    $amodel->insert();
  }

  public function testSave() {
    $this->dataConnectionMock->expects($this->once())
      ->method('save')
      ->with('modelas', array(
        'init_id' => '5',
        'partner' => array(
          'id' => 1,
          'name' => 'test',
          'testOwner' => NULL
        ),
        'id' => 2
      ));

    /**
     * @var $amodel \mabiTesting\ModelA
     */
    $amodel = \mabiTesting\ModelA::init($this->app);
    $amodel->id = 2;
    $amodel->init_id = '5';
    $bmodel = \mabiTesting\ModelB::init($this->app);
    $bmodel->modelBId = 1;
    $bmodel->name = 'test';
    $amodel->partner = $bmodel;
    $amodel->save();
  }

  public function testDelete() {
    $this->dataConnectionMock->expects($this->once())
      ->method('deleteByField')
      ->with('id', 5, 'modelas');

    $amodel = \mabiTesting\ModelA::init($this->app);
    $amodel->id = 5;
    $amodel->delete();
  }

  public function testClearAll() {
    $this->dataConnectionMock->expects($this->once())
      ->method('clearAll')
      ->with('modelas');

    $amodel = \mabiTesting\ModelA::init($this->app);
    $amodel->clearAll();
  }

  /**
   * test all field types in FullModel
   */
  public function testAllFieldTypes() {
    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('id', 1, 'fullmodels')
      ->will($this->returnValue(array(
        'id' => 1,
        'init_id' => '2',
        'intField' => '10',
        'boolField' => '0',
        'floatField' => '10.5',
        'timestampField' => '1368021565',
        'arrayField' => array('10', '12', '23', 7),
        'subObjList' => array(
          array('modelBId' => 1, 'name' => 'test'),
          array('modelBId' => 2, 'name' => 'test2')
        ),
      )));

    /**
     * @var $fmodel \mabiTesting\FullModel
     */
    $fmodel = \mabiTesting\FullModel::init($this->app);
    $fmodel->findById(1);
    $this->assertNotEmpty($fmodel);
    $this->assertInternalType('string', $fmodel->init_id);
    $this->assertEquals('2', $fmodel->init_id);
    $this->assertInternalType('int', $fmodel->intField);
    $this->assertEquals(10, $fmodel->intField);
    $this->assertInternalType('bool', $fmodel->boolField);
    $this->assertEquals(FALSE, $fmodel->boolField);
    $this->assertInternalType('float', $fmodel->floatField);
    $this->assertEquals(10.5, $fmodel->floatField);
    $this->assertInstanceOf('DateTime', $fmodel->timestampField);
    $this->assertEquals(new \DateTime('2013-05-08 13:59:25+00:00'), $fmodel->timestampField);
    $this->assertInternalType('array', $fmodel->arrayField);
    $this->assertEquals(array('10', '12', '23', 7), $fmodel->arrayField);
    $this->assertInternalType('array', $fmodel->subObjList);
    $this->assertCount(2, $fmodel->subObjList);
    $this->assertInstanceOf('\mabiTesting\ModelB', $fmodel->subObjList[0]);
    $this->assertInstanceOf('\mabiTesting\ModelB', $fmodel->subObjList[0]);
    $this->assertNotEmpty($fmodel->subObjList[0]);
    $this->assertNotEmpty($fmodel->subObjList[0]->name);
  }

  // todo: test external, system, and internal fields
  // todo: test remaining results
}