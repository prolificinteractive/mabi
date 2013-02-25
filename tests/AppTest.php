<?php

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/../MongoDataConnection.php';

class AppTest extends PHPUnit_Framework_TestCase {

  public function setUp() {
    \Slim\Environment::mock();
  }

  public function testNew() {
    $app = new \MABI\App();
    $connection = \MABI\MongoDataConnection::create('localhost', '27017', 'foodTweeks');
    $app->addDataConnection('default', $connection);
    $this->assertNotEmpty($app->getDataConnection('default'));
    $this->assertInstanceOf('MABI\MongoDataConnection',$app->getDataConnection('default'));
  }
}