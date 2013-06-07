<?php

namespace MABI\Testing;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/../MongoDataConnection.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/../DirectoryControllerLoader.php';
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

  function testConfigSettings() {
    $app = new \MABI\App();
    $app->setConfig('testkey1', 'valA');
    $outConfig = $app->getConfig('testkey1');
    $this->assertEquals('valA', $outConfig);
  }
}