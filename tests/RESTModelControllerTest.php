<?php

namespace MABI\Testing;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/../Utilities.php';
include_once __DIR__ . '/../DataConnection.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/../DirectoryControllerLoader.php';
include_once __DIR__ . '/../GeneratedRESTModelControllerLoader.php';
include_once __DIR__ . '/../DirectoryModelLoader.php';
include_once __DIR__ . '/../autodocs/MarkdownParser.php';

class RESTModelControllerTest extends \PHPUnit_Framework_TestCase {
  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $dataConnectionMock;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $controllerMock;

  /**
   * @var \MABI\App
   */
  protected $app;

  // note: All controller loaders tested together is tested in the AppTest
  public function testGeneratedRESTModelControllerLoader() {
    \Slim\Environment::mock();
    $this->app = new \MABI\App();
    $this->app->setModelLoaders(array(new \MABI\DirectoryModelLoader(__DIR__ . '/TestApp/TestModelDir', 'mabiTesting')));
    $this->app->getExtensionModelClasses();

    $controllerLoader = new \MABI\GeneratedRESTModelControllerLoader(array(0 => 'mabiTesting\ModelA'), $this->app);
    $controllers = $controllerLoader->getControllers();
    $this->assertNotEmpty($controllers);
    $this->assertInstanceOf('\MABI\RESTModelController', $controllers[0]);

    // todo: assert that generated rest model controller carried over middlewares
  }

  private function setUpRESTApp($env = array()) {
    \Slim\Environment::mock($env);
    $this->app = new \MABI\App();

    $this->dataConnectionMock = $this->getMock('\MABI\DataConnection');
    $this->dataConnectionMock
      ->expects($this->any())
      ->method('getDefaultIdColumn')
      ->will($this->returnValue('id'));

    $this->app->addDataConnection('default', $this->dataConnectionMock);

    $this->app->setModelLoaders(array(new \MABI\DirectoryModelLoader(__DIR__ . '/TestApp/TestModelDir', 'mabiTesting')));

    $dirControllerLoader = new \MABI\DirectoryControllerLoader('TestApp/TestControllerDir', $this->app, 'mabiTesting');

    $this->controllerMock = $this->getMock('\mabiTesting\ModelBController', array(
        'restGetTestFunc',
        'restPostTestFunc',
        'restPutTestFunc',
        'restDeleteTestFunc'
      ), array($this->app),
      'ModelBController');

    // Set up modelClass and base fields in the mock controller
    $modelClass = 'mabiTesting\ModelB';
    $refObject = new \ReflectionObject($this->controllerMock);
    $refModelClassProperty = $refObject->getProperty('modelClass');
    $refModelClassProperty->setAccessible(TRUE);
    $refModelClassProperty->setValue($this->controllerMock, $modelClass);
    $refBaseProperty = $refObject->getProperty('base');
    $refBaseProperty->setAccessible(TRUE);
    $refBaseProperty->setValue($this->controllerMock, strtolower(\MABI\ReflectionHelper::stripClassName($modelClass)));

    $controllerLoader = new \MABI\ControllerLoader();
    $controllerLoader->setControllers(array($this->controllerMock));

    $this->app->setControllerLoaders(array(
      $controllerLoader,
      new \MABI\GeneratedRESTModelControllerLoader(
        array_diff($this->app->getExtensionModelClasses(), array($modelClass)), $this->app)
    ));
  }

  public function testStandardRESTRoutes() {
    // Test GET /{collection}
    $this->setUpRESTApp(array('PATH_INFO' => '/modelas'));
    $this->dataConnectionMock->expects($this->once())
      ->method('findAll')
      ->with('modelas')
      ->will($this->returnValue(array(
        array(
          'id' => 1,
          'init_id' => '2',
          'partner' => array('modelBId' => 1, 'name' => 'test')
        )
      )));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $result = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($result);
    $this->assertInternalType('array', $result);

    // Test POST /{collection}
    $this->setUpRESTApp(array(
      'REQUEST_METHOD' => 'POST',
      'slim.input' => '{"name":"modelb"}',
      'PATH_INFO' => '/modelb'
    ));
    $this->dataConnectionMock->expects($this->once())
      ->method('insert')
      ->with('modelbs', array(
        'name' => 'modelb',
        'testOwner' => NULL
      ))
      ->will($this->returnValue(array(
        array(
          'modelBId' => 2,
          'name' => 'modelb',
        )
      )));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $result = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($result);
    $this->assertInstanceOf('stdClass', $result);

    // Test GET /{collection}/{objectid}
    $this->setUpRESTApp(array('PATH_INFO' => '/modelas/1'));
    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('id', 1, 'modelas')
      ->will($this->returnValue(array(
        'id' => 1,
        'init_id' => '2',
        'partner' => array('modelBId' => 1, 'name' => 'test')
      )));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $result = json_decode($this->app->getResponse()->body());
    $this->assertNotEmpty($result);
    $this->assertInstanceOf('stdClass', $result);

    // todo: test the rest of the standard REST routes once functioning
  }

  public function testCustomRESTRoutes() {
    // Test custom get
    $this->setUpRESTApp(array('PATH_INFO' => '/modelb/1/testfunc'));
    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('id', 1, 'modelbs')
      ->will($this->returnValue(array(
        'modelBId' => 1,
        'name' => 'test'
      )));
    $this->controllerMock->expects($this->once())
      ->method('restGetTestFunc')
      ->will($this->returnValue('test'));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertEquals('', $this->app->getResponse()->body());

    // Test custom post
    $this->setUpRESTApp(array('REQUEST_METHOD' => 'POST', 'PATH_INFO' => '/modelb/1/testfunc'));
    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('id', 1, 'modelbs')
      ->will($this->returnValue(array(
        'modelBId' => 1,
        'name' => 'test'
      )));
    $this->controllerMock->expects($this->once())
      ->method('restPostTestFunc')
      ->will($this->returnValue('test'));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertEquals('', $this->app->getResponse()->body());

    // Test custom delete
    $this->setUpRESTApp(array('REQUEST_METHOD' => 'DELETE', 'PATH_INFO' => '/modelb/1/testfunc'));
    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('id', 1, 'modelbs')
      ->will($this->returnValue(array(
        'modelBId' => 1,
        'name' => 'test'
      )));
    $this->controllerMock->expects($this->once())
      ->method('restDeleteTestFunc')
      ->will($this->returnValue('test'));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertEquals('', $this->app->getResponse()->body());

    // Test custom put
    $this->setUpRESTApp(array('REQUEST_METHOD' => 'PUT', 'PATH_INFO' => '/modelb/1/testfunc'));
    $this->dataConnectionMock->expects($this->once())
      ->method('findOneByField')
      ->with('id', 1, 'modelbs')
      ->will($this->returnValue(array(
        'modelBId' => 1,
        'name' => 'test'
      )));
    $this->controllerMock->expects($this->once())
      ->method('restPutTestFunc')
      ->will($this->returnValue('test'));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertEquals('', $this->app->getResponse()->body());
  }

  /**
   * make sure document generator returns valid doc array with no errors/warnings
   */
  function testDocs() {
    $this->setUpRESTApp();
    $parser = new \MABI\Autodocs\MarkdownParser();
    $docsOutput = $this->controllerMock->getDocJSON($parser);
    $this->assertNotEmpty($docsOutput);
    $this->assertInternalType('array', $docsOutput);
  }
}