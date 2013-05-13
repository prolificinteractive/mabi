<?php

namespace MABI;

include_once __DIR__ . '/Utilities.php';
include_once __DIR__ . '/ControllerLoader.php';
include_once __DIR__ . '/ModelController.php';

class DirectoryControllerLoader extends ControllerLoader {

  /**
   * @var \MABI\App
   */
  protected $app;

  /**
   * @var string
   */
  protected $directory;

  /**
   * @var string
   */
  protected $namespace = '';

  /**
   * @var string[]
   */
  protected $controllerClasses = array();

  /**
   * @var string[]
   */
  protected $overriddenModelClasses = array();

  public function __construct($directory, $app, $namespace = NULL) {
    $this->app = $app;
    $this->directory = $directory;
    $this->namespace = empty($namespace) ? '' : $namespace;

    $controllerClassFiles = DirectoryHelper::directoryToArray($this->directory, TRUE, '.php');

    foreach ($controllerClassFiles as $controllerClassFile) {
      include_once $controllerClassFile;

      $controllerClass = ReflectionHelper::createClassName($this->namespace, basename($controllerClassFile, '.php'));
      $this->controllerClasses[] = $controllerClass;

      $controller = new $controllerClass($this->app);
      $rclass = new \ReflectionClass($controller);
      if ($rclass->isSubclassOf('\MABI\ModelController')) {
        /**
         * @var $controller \MABI\ModelController
         */
        $this->overriddenModelClasses[] = $controller->getModelClass();
      }
      $this->controllers[] = $controller;
    }
  }

  public function getOverriddenModelClasses() {
    return $this->overriddenModelClasses;
  }

  /**
   * @return Controller[]
   */
  public function getControllers() {
    return $this->controllers;
  }

}