<?php

namespace MABI\Testing;

use MABI\App;
use MABI\Autodocs\MarkdownParser;
use MABI\DirectoryControllerLoader;
use MABI\DirectoryModelLoader;
use MABI\Extension;

include_once __DIR__ . '/../Extension.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/../DirectoryControllerLoader.php';
include_once __DIR__ . '/../GeneratedRESTModelControllerLoader.php';
include_once __DIR__ . '/AppTestCase.php';

class ExtensionTest extends AppTestCase {

  public function setUp() {
    \Slim\Environment::mock();
  }

  function testSetMiddlewareDirectories() {
    $app = new App();
    $middlewareDirs = array('/testdir/1', '/testdir/2');
    $app->setMiddlewareDirectories($middlewareDirs);
    $this->assertEquals($middlewareDirs, $app->getMiddlewareDirectories());
  }

  function testGetMiddlewareDirectories() {
    $app = new App();
    $newExt = new Extension($app);
    $app->setMiddlewareDirectories(array('/testdir/1', '/testdir/2'));
    $newExt->setMiddlewareDirectories(array('/testdir/3'));
    $app->addExtension($newExt);
    $this->assertCount(3, $app->getMiddlewareDirectories());
    $this->assertContains('/testdir/1', $app->getMiddlewareDirectories());
    $this->assertContains('/testdir/2', $app->getMiddlewareDirectories());
    $this->assertContains('/testdir/3', $app->getMiddlewareDirectories());
  }

  function testGetConfig() {
    $app = new App();
    $newExt = new Extension($app);
    $newExt->setConfig('test2', 'valB');
    $newExt->setConfig('test1', 'valC');
    $app->addExtension($newExt);
    $app->setConfig('test1', 'valA');

    $this->assertEquals('valA', $app->getConfig('test1'));
    $this->assertEquals('valB', $app->getConfig('test2'));
  }

  function testGetModelClasses() {
    $app = new App();
    $newExt = new Extension($app);
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
    $this->setUpApp();

    $newExt = new Extension($this->app);

    $this->app->setModelLoaders(array(new DirectoryModelLoader(__DIR__ . '/TestApp/TestModelDir', 'mabiTesting')));

    $this->app->setMiddlewareDirectories(array(__DIR__ . '/../middleware'));
    $this->app->setControllerLoaders(array(
      new DirectoryControllerLoader('TestApp/TestControllerDir', $this->app,
        'mabiTesting')
    ));
    $newExt->setControllerLoaders(array(
      new DirectoryControllerLoader('TestApp/TestExtensionDir/TestControllerDir',
        $this->app, 'mabiTesting\testExtension')
    ));
    $this->app->addExtension($newExt);

    $outControllerClasses = array();

    foreach ($this->app->getControllers() as $controller) {
      $outControllerClasses[] = get_class($controller);
    }

    $this->assertContains('mabiTesting\testExtension\ModelCController', $outControllerClasses);
    $this->assertContains('mabiTesting\JustAController', $outControllerClasses);
    $this->assertContains('mabiTesting\ModelBController', $outControllerClasses);
    $this->assertCount(3, $outControllerClasses);
  }

  function testGetDocJSON() {
    $this->setUpApp();

    $this->app->setModelLoaders(array(new DirectoryModelLoader(__DIR__ . '/TestApp/TestModelDir', 'mabiTesting')));

    $this->app->setControllerLoaders(array(new DirectoryControllerLoader('TestApp/TestControllerDir', $this->app, 'mabiTesting')));
    $parser = new MarkdownParser();
    $docsOutput = $this->app->getDocJSON($parser);

    $this->assertNotEmpty($docsOutput);
    $this->assertInternalType('array', $docsOutput);
  }
}