<?php

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/../MongoDataConnection.php';

class AppTest extends PHPUnit_Framework_TestCase {

  public function testNew() {
    $app = new \MABI\App();
    $connection = \MABI\MongoDataConnection::create('localhost', '27017', 'foodTweeks');
    $app->addDataConnection('default', $connection);
    var_dump($app->getDataConnection('default'));
  }
}