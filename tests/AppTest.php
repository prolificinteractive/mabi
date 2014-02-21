<?php

namespace MABI\Testing;

include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/../MongoDataConnection.php';
include_once __DIR__ . '/../GeneratedRESTModelControllerLoader.php';
include_once __DIR__ . '/../autodocs/MarkdownParser.php';

class AppTest extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    \Slim\Environment::mock();
  }

  /**
   * make sure document generator returns valid doc array with no errors/warnings
   */
  function testDocs() {
    $app = new \MABI\App();
    $parser = new \MABI\Autodocs\MarkdownParser();
    $docsOutput = $app->getDocJSON($parser);
    $this->assertInternalType('array', $docsOutput);
  }

  function testSingleton() {
    $app = \MABI\App::getSingletonApp();
    $this->assertNotEmpty($app);
    $this->assertEquals(\MABI\App::getSingletonApp(), $app);
  }

  function testClearSingleton() {
    $sing1App = \MABI\App::getSingletonApp();
    \MABI\App::clearSingletonApp();
    $sing2App = \MABI\App::getSingletonApp();
    $this->assertNotEquals($sing2App, $sing1App);
  }

  function testConfigSettings() {
    $app = new \MABI\App();
    $app->setConfig('testkey1', 'valA');
    $outConfig = $app->getConfig('testkey1');
    $this->assertEquals('valA', $outConfig);
  }
}