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
      $rclass = new \ReflectionClass($modelClass);
      $properties = ReflectionHelper::getDocProperty($rclass->getDocComment(), 'restful');
      if (!in_array('NoController', $properties)) {
        $this->controllers[] = RESTModelController::generate($modelClass, $this->app);
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