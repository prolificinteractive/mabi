<?php

namespace MABI;

include_once dirname(__FILE__) . '/Utilities.php';
include_once dirname(__FILE__) . '/ControllerLoader.php';

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
  protected $modelControllerClasses = array();

  /**
   * @var \MABI\Controller[]
   */
  protected $controllers = array();

  public function __construct($directory, $app, $namespace = NULL) {
    $this->app = $app;
    $this->directory = $directory;
    $this->namespace = empty($namespace) ? '' : $namespace;
  }

  public function getModelControllerClasses() {
    return $this->modelControllerClasses;
  }

  /**
   * @return Controller[]
   */
  public function loadControllers() {
    // TODO: Implement loadControllers() method.
    $controllerClassFiles = DirectoryHelper::directoryToArray($this->directory, TRUE, '.php');

    foreach ($controllerClassFiles as $controllerClassFile) {
      include_once $controllerClassFile;

      $controllerClass = ReflectionHelper::createClassName($this->namespace, basename($controllerClassFile, '.php'));
      $this->controllerClasses[] = $controllerClass;

      $rclass = new \ReflectionClass($controllerClass);
      if ($rclass->isSubclassOf('\MABI\ModelController')) {
        $this->modelControllerClasses[] = $controllerClass;
      }
      $this->controllers[] = new $controllerClass($this->app);
    }

    return $this->controllers;
  }

}