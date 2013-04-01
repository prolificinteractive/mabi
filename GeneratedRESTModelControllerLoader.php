<?php

namespace MABI;

include_once dirname(__FILE__) . '/ControllerLoader.php';
include_once dirname(__FILE__) . '/RESTModelController.php';

/**
 * automatically generates RESTful controllers
 * GET      /<model>          get all models by id
 * POST     /<model>          creates a new model
 * PUT      /<model>          bulk creates full model collection
 * DELETE   /<model>          deletes all models
 * GET      /<model>/<id>     gets one model's full details
 * PUT      /<model>/<id>     updates the model
 * DELETE   /<model>/<id>     deletes the model
 *
 * These functions will be restricted by AccessControl
 */
class GeneratedRESTModelControllerLoader extends ControllerLoader {

  /**
   * @var \MABI\App
   */
  protected $app;

  /**
   * @var string[]
   */
  protected $modelClasses;

  /**
   * @var \MABI\Controller[]
   */
  protected $controllers = array();

  public function __construct($modelClasses, $app) {
    $this->app = $app;
    $this->modelClasses = $modelClasses;

    foreach ($this->modelClasses as $modelClass) {
      $rClass = new \ReflectionClass($modelClass);
      $properties = ReflectionHelper::getDocProperty($rClass->getDocComment(), 'restful');
      if (!in_array('NoController', $properties)) {
        /**
         * @var $controller Controller
         */
        $controller = RESTModelController::generate($modelClass, $this->app);

        $middlewares = ReflectionHelper::getDocProperty($rClass->getDocComment(), 'middleware');
        foreach ($middlewares as $middlewareClass) {
          $middlewareFile = ReflectionHelper::stripClassName($middlewareClass) . '.php';
          include_once dirname(__FILE__) . '/middleware/' . $middlewareFile;

          /**
           * @var $middleware \MABI\Middleware
           */
          $middleware = new $middlewareClass();
          $controller->addMiddleware($middleware);
        }
        $this->controllers[] = $controller;
      }
    }
  }

  /**
   * @return Controller[]
   */
  function getControllers() {
    // TODO: Implement getControllers() method.
    return $this->controllers;
  }

}