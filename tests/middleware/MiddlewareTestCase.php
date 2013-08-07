<?php

namespace MABI\Testing;

include_once 'PHPUnit/Autoload.php';
include_once __DIR__ . '/../../middleware/AnonymousIdentifier.php';
include_once __DIR__ . '/../../DirectoryControllerLoader.php';
include_once __DIR__ . '/../../DirectoryModelLoader.php';

class MiddlewareTestCase extends \PHPUnit_Framework_TestCase {

  /**
   * @var \MABI\App
   */
  protected $app;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $dataConnectionMock;

  /**
   * @var \MABI\Controller
   */
  protected $controller;

  public function setUpApp($env = array(), $middlewares = array()) {
    \Slim\Environment::mock($env);
    $this->app = new \MABI\App();

    $dirControllerLoader = new \MABI\DirectoryControllerLoader(__DIR__ . '/../TestApp/TestControllerDir', $this->app,
      'mabiTesting');
    foreach ($dirControllerLoader->getControllers() as $controller) {
      if (get_class($controller) == 'mabiTesting\JustAController') {
        $this->controller = $controller;
        if (!empty($middlewares)) {
          foreach ($middlewares as $middleware) {
            $this->controller->addMiddleware($middleware);
          }
          $this->controller->addMiddleware(new \MABI\Middleware\AnonymousIdentifier());
        }
      }
    }

    $this->dataConnectionMock = $this->getMock('\MABI\DataConnection');
    $this->dataConnectionMock
      ->expects($this->any())
      ->method('getDefaultIdColumn')
      ->will($this->returnValue('id'));

    $this->app->addDataConnection('default', $this->dataConnectionMock);

    $this->app->setControllerLoaders(array($dirControllerLoader));
  }

  /**
   * @var \MABI\RESTModelController
   */
  protected $restController;

  public function setUpRESTApp($env = array(), $middlewares = array()) {
    \Slim\Environment::mock($env);
    $this->app = new \MABI\App();

    $this->dataConnectionMock = $this->getMock('\MABI\Testing\MockDataConnection',
      array('findOneByField', 'query', 'insert', 'save', 'deleteByField', 'clearAll', 'getNewId', 'findAll')
    );

    $this->app->addDataConnection('default', $this->dataConnectionMock);

    $modelLoader = new \MABI\DirectoryModelLoader(__DIR__ . '/../TestApp/TestModelDir', 'mabiTesting');
    $modelLoader->loadModels();
    $this->app->setModelLoaders(array($modelLoader));

    $dirControllerLoader = new \MABI\DirectoryControllerLoader(__DIR__ . '/../TestApp/TestControllerDir', $this->app,
      'mabiTesting');
    foreach ($dirControllerLoader->getControllers() as $controller) {
      if (get_class($controller) == 'mabiTesting\ModelBController') {
        $this->restController = $controller;
        if (!empty($middlewares)) {
          foreach ($middlewares as $middleware) {
            $this->restController->addMiddleware($middleware);
          }
          $this->restController->addMiddleware(new \MABI\Middleware\AnonymousIdentifier());
        }
      }
    }

    $this->app->setControllerLoaders(array($dirControllerLoader));
  }

}