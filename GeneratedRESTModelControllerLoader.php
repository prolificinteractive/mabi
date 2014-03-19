<?php

namespace MABI;

include_once __DIR__ . '/ControllerLoader.php';
include_once __DIR__ . '/RESTModelController.php';

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
   * @var \MABI\Extension
   */
  protected $extension;

  /**
   * @var string[]
   */
  protected $modelClasses;

  /**
   * @var \MABI\Controller[]
   */
  protected $controllers = array();

  public function __construct(array $modelClasses,Extension $extension) {
    $this->extension = $extension;
    $this->modelClasses = $modelClasses;

    $annotationReader = $extension->getApp()->getAnnotationReader();

    foreach ($this->modelClasses as $modelClass) {
      $rClass = new \ReflectionClass($modelClass);

      if (!$annotationReader->getClassAnnotation($rClass, 'MABI\Annotations\Model\NoController')) {
        // If annotation is present, do not generate controller

        /**
         * @var $controller Controller
         */
        $controller = RESTModelController::generate($modelClass, $this->extension);

        // Load the middleware that's specified in the Model
        $annotations = $annotationReader->getClassAnnotations($rClass);
        foreach ($annotations as $annotation) {
          if ($annotation instanceof \MABI\Annotations\Middleware) {
            /**
             * @var $annotation \MABI\Annotations\Middleware
             */
            $middlewareFile = ReflectionHelper::stripClassName($annotation->value) . '.php';
            $controller->addMiddlewareByClass($annotation->value, $middlewareFile);
          }
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