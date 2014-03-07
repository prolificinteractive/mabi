<?php

namespace MABI\Testing;

include_once __DIR__ . '/../../middleware/AnonymousIdentifier.php';
include_once __DIR__ . '/../../DirectoryControllerLoader.php';
include_once __DIR__ . '/../../DirectoryModelLoader.php';
include_once __DIR__ . '/../AppTestCase.php';

class MiddlewareTestCase extends AppTestCase {

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

  /**
   * @var \MABI\RESTModelController
   */
  protected $restController;

  /**
   * Special override to set up the app with controllers that have a dynamic middleware. This is better suited to
   * testing Middleware so that the developer can pass in the middleware and subsequently perform tests on it
   *
   * @param array $env
   * @param array $middlewares
   */
  public function setUpApp($env = array(), $middlewares = array()) {
    parent::setUpApp($env);

    $this->app->setModelLoaders(array(new \MABI\DirectoryModelLoader(__DIR__ . '/../TestApp/TestModelDir', $this->app,
      'mabiTesting')));

    $dirControllerLoader = new \MABI\DirectoryControllerLoader(__DIR__ . '/../TestApp/TestControllerDir', $this->app,
      'mabiTesting');
    foreach ($dirControllerLoader->getControllers() as $controller) {
      foreach ($middlewares as $middleware) {
        $controller->addMiddleware($middleware);
      }
      $controller->addMiddleware(new \MABI\Middleware\AnonymousIdentifier());

      if (get_class($controller) == 'mabiTesting\JustAController') {
        $this->controller = $controller;
      }
      elseif (get_class($controller) == 'mabiTesting\ModelBController') {
        $this->restController = $controller;
      }
    }

    $this->app->setControllerLoaders(array($dirControllerLoader));
  }

}