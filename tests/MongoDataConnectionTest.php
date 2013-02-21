<?php

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../MongoDataConnection.php';

class MongoDataConnectionTest extends PHPUnit_Framework_TestCase {

  public function testCreate() {
    $connection = \MABI\MongoDataConnection::create('localhost', '27017', 'foodTweeks');
    $this->assertNotEmpty($connection);
  }

  public function testFindOneByField() {
    $connection = \MABI\MongoDataConnection::create('localhost', '27017', 'foodTweeks');
//    $result = $connection->findOneByField('init_id', 23, 'tweeks');
    $result = $connection->findOneByField('_id', new MongoId('511d07c4cfc422868a000016'), 'tweeks');
    var_dump($result);
    $this->assertInternalType('array', $result);
  }
}