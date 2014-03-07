<?php

namespace MABI;

include_once __DIR__ . '/Utilities.php';
include_once __DIR__ . '/ControllerLoader.php';
include_once __DIR__ . '/ModelController.php';

class DirectoryControllerLoader extends ControllerLoader {

  /**
   * @var \MABI\Extension
   */
  protected $extension;

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

  public function __construct($directory, $extension, $namespace = NULL) {
    $this->extension = $extension;
    $this->directory = $directory;
    $this->namespace = empty($namespace) ? '' : $namespace;

    if (($systemCache = $this->extension->getApp()->getCacheRepository('system')) != NULL &&
      is_array($controllerClassFiles = $systemCache->get($this->directory . '::fileList'))
    ) {

    }
    else {
      // Make sure all PHP files in the directory are included
      $controllerClassFiles = DirectoryHelper::directoryToArray($this->directory, TRUE, '.php');
      if ($systemCache != NULL) {
        $systemCache->forever($this->directory . '::fileList', $controllerClassFiles);
      }
    }

    foreach ($controllerClassFiles as $controllerClassFile) {
      include_once $controllerClassFile;

      $controllerClass = ReflectionHelper::createClassName($this->namespace, basename($controllerClassFile, '.php'));
      $this->controllerClasses[] = $controllerClass;

      $controller = new $controllerClass($this->extension);
      if ($controller instanceof ModelController) {
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