<?php

namespace abcd;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/../Model.php';
include_once __DIR__ . '/../MongoDataConnection.php';

class ATestModel extends \MABI\Model {
  /**
   * @var string
   */
  public $init_id;

  /**
   * @var \abcd\BTestModel
   */
  public $description;

  protected $table = 'tweeks';
}

class BTestModel extends ATestModel {

}

class ModelTest extends \PHPUnit_Framework_TestCase {

  public function testInit() {
    \Slim\Environment::mock();

    $app = new \MABI\App();
    $app->addDataConnection('default', \MABI\MongoDataConnection::create('localhost', '27017', 'foodTweeks'));
    $amodel = ATestModel::init($app);
    $this->assertNotEmpty($amodel);
  }

  public function testFindById() {
    $app = new \MABI\App();
    $app->addDataConnection('default', \MABI\MongoDataConnection::create('localhost', '27017', 'foodTweeks'));
    $amodel = ATestModel::init($app);
    $amodel->findById(new \MongoId('511d07c4cfc422868a000016'));
//    var_dump($amodel);
  }
}