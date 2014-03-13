<?php

namespace MABI\Testing;

include_once __DIR__ . '/../Model.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/../DataConnection.php';
include_once __DIR__ . '/SampleAppTestCase.php';

class ModelTest extends SampleAppTestCase {

  /**
   * @var \mabiTesting\ModelA
   */
  protected $insertedModel;

  public function setUp() {
    $this->setUpApp();
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
   * test get all field types in FullModel
   */
  public function testGetAllFieldTypes() {
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

  public function testLoadFromJSONError1() {
    $this->setExpectedException('Exception');
    /**
     * @var $fmodel \mabiTesting\FullModel
     */
    $fmodel = \mabiTesting\FullModel::init($this->app);
    $fmodel->loadFromExternalSource('{invalid:"json"}');
  }

  public function testLoadFromJSONError2() {
    $this->setExpectedException('Exception');
    /**
     * @var $fmodel \mabiTesting\FullModel
     */
    $fmodel = \mabiTesting\FullModel::init($this->app);
    $fmodel->loadFromExternalSource('null');
  }

  public function testCount(){
    $count = 12;
    $this->dataConnectionMock->expects($this->once())
      ->method('count')
      ->with('modelas')
      ->will($this->returnValue($count));

    /**
     * @var $amodel \mabiTesting\ModelA
     */
    $amodel = \mabiTesting\ModelA::init($this->app);
    $response_count = $amodel->count($amodel);
    $this->assertEquals(12, $response_count);
  }

  // todo: public function testInsertAllFieldTypes() {
  // todo: test external, system, and internal fields
  // todo: test remaining results
}