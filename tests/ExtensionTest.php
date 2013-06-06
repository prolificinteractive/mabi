<?php

namespace MABI\Testing;

use MABI\Autodocs\MarkdownParser;
use MABI\DirectoryControllerLoader;
use MABI\DirectoryModelLoader;
use MABI\Extension;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../Extension.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/../DirectoryControllerLoader.php';
include_once __DIR__ . '/../GeneratedRESTModelControllerLoader.php';
include_once __DIR__ . '/../autodocs/MarkdownParser.php';
include_once __DIR__ . '/../autodocs/MarkdownParser.php';

class ExtensionTest extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    \Slim\Environment::mock();
  }

  function testSetMiddlewareDirectories() {
    $app = new Extension();
    $middlewareDirs = array('/testdir/1', '/testdir/2');
    $app->setMiddlewareDirectories($middlewareDirs);
    $this->assertEquals($middlewareDirs, $app->getMiddlewareDirectories());
  }

  function testGetMiddlewareDirectories() {
    $app = new Extension();
    $newExt = new Extension();
    $app->setMiddlewareDirectories(array('/testdir/1', '/testdir/2'));
    $newExt->setMiddlewareDirectories(array('/testdir/3'));
    $app->addExtension($newExt);
    $this->assertCount(3, $app->getMiddlewareDirectories());
    $this->assertContains('/testdir/1', $app->getMiddlewareDirectories());
    $this->assertContains('/testdir/2', $app->getMiddlewareDirectories());
    $this->assertContains('/testdir/3', $app->getMiddlewareDirectories());
  }

  function testGetConfig() {
    $app = new Extension();
    $newExt = new Extension();
    $newExt->setConfig('test2', 'valB');
    $newExt->setConfig('test1', 'valC');
    $app->addExtension($newExt);
    $app->setConfig('test1', 'valA');

    $this->assertEquals('valA', $app->getConfig('test1'));
    $this->assertEquals('valB', $app->getConfig('test2'));
  }

  function testGetModelClasses() {
    $app = new Extension();
    $newExt = new Extension();
    $app->setModelLoaders(array(new DirectoryModelLoader('TestApp/TestModelDir', 'mabiTesting')));
    $newExt->setModelLoaders(array(new DirectoryModelLoader('TestApp/TestExtensionDir/TestModelDir', 'mabiTesting\testExtension')));
    $app->addExtension($newExt);

    $outClasses = $app->getModelClasses();

    $this->assertContains('mabiTesting\testExtension\ModelC', $outClasses);
    $this->assertContains('mabiTesting\FullModel', $outClasses);
    $this->assertContains('mabiTesting\ModelA', $outClasses);
    $this->assertContains('mabiTesting\ModelB', $outClasses);
    $this->assertCount(4, $outClasses);
  }

  function testSetModelLoaders() {
    $app = new Extension();
    $newExt = new Extension();
    $app->setMiddlewareDirectories(array(__DIR__ . '/../middleware'));
    $app->setControllerLoaders(array(new DirectoryControllerLoader('TestApp/TestControllerDir', $app, 'mabiTesting')));
    $newExt->setControllerLoaders(array(new DirectoryControllerLoader('TestApp/TestExtensionDir/TestControllerDir', $app, 'mabiTesting\testExtension')));
    $app->addExtension($newExt);

    $outControllerClasses = array();

    foreach($app->getControllers() as $controller) {
      $outControllerClasses[] = get_class($controller);
    }

    $this->assertContains('mabiTesting\testExtension\ModelCController', $outControllerClasses);
    $this->assertContains('mabiTesting\JustAController', $outControllerClasses);
    $this->assertContains('mabiTesting\ModelBController', $outControllerClasses);
    $this->assertCount(3, $outControllerClasses);
  }

  function testGetDocJSON() {
    $app = new Extension();
    $app->setControllerLoaders(array(new DirectoryControllerLoader('TestApp/TestControllerDir', $app, 'mabiTesting')));
    $parser = new MarkdownParser();
    $docsOutput = $app->getDocJSON($parser);

    $this->assertNotEmpty($docsOutput);
    $this->assertInternalType('array', $docsOutput);
  }
}