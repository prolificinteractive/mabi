<?php

namespace MABI;

include_once __DIR__ . '/DataConnection.php';
include_once __DIR__ . '/ModelLoader.php';
include_once __DIR__ . '/Controller.php';
include_once __DIR__ . '/ControllerLoader.php';

interface Parser {
  function Parse($text);
}

/**
 * todo: docs
 */
class Extension {

  /**
   * @var DataConnection[]
   */
  protected $dataConnections = array();

  /**
   * @var ModelLoader[]
   */
  protected $modelLoaders = array();

  /**
   * @var ControllerLoader[]
   */
  protected $controllerLoaders = array();

  /**
   * @var Array
   */
  protected $config = array();

  /**
   * @var Extension[]
   */
  protected $extensions = array();

  /**
   * @var App
   */
  protected $app;

  /**
   * @return \MABI\App
   */
  public function getApp() {
    return $this->app;
  }

  /**
   * @var string[]
   */
  protected $middlewareDirectories = array();

  /**
   * @param $middlewareDirectories string[]
   */
  public function setMiddlewareDirectories($middlewareDirectories) {
    $this->middlewareDirectories = $middlewareDirectories;
  }

  /**
   * @return string[]
   */
  public function getMiddlewareDirectories() {
    $extensionDirs = array();
    foreach ($this->extensions as $extension) {
      $extensionDirs = array_merge($extensionDirs, $extension->getMiddlewareDirectories());
    }

    return array_merge($extensionDirs, $this->middlewareDirectories);
  }

  /**
   * @param $app App
   */
  public function __construct($app) {
    $this->app = $app;
  }

  /**
   * todo: docs
   *
   * @param $name string
   * @param $dataConnection DataConnection
   */
  function addDataConnection($name, $dataConnection) {
    $this->dataConnections[$name] = $dataConnection;
  }

  /**
   * todo: docs
   *
   * @param $key
   * @param $value
   */
  public function setConfig($key, $value) {
    $this->config[$key] = $value;
  }

  /**
   * todo: docs
   *
   * @param $key
   *
   * @return mixed
   */
  public function getConfig($key) {
    $extensionConfigs = array();
    foreach ($this->extensions as $extension) {
      $extensionConfigs = array_merge($extensionConfigs, $extension->config);
    }

    $configs = array_merge($extensionConfigs, $this->config);
    return $configs[$key];
  }

  /**
   * todo: docs
   *
   * @param $modelClass string
   */
  function addModel($modelClass) {
    // todo: implement
  }

  /**
   * todo: docs
   *
   * @param $modelLoaders ModelLoader[]
   */
  public function setModelLoaders(array $modelLoaders) {
    $this->modelLoaders = $modelLoaders;
  }

  /**
   * @param $name
   *
   * @return DataConnection
   */
  public function getDataConnection($name) {
    $extensionDataConnections = array();
    foreach ($this->extensions as $extension) {
      $extensionDataConnections = array_merge($extensionDataConnections, $extension->dataConnections);
    }

    $dataConnections = array_merge($extensionDataConnections, $this->dataConnections);
    return $dataConnections[$name];
  }

  public function getExtensionModelClasses() {
    $modelClasses = array();

    foreach ($this->modelLoaders as $modelLoader) {
      $loadModelClasses = $modelLoader->loadModels();
      foreach ($loadModelClasses as $modelClass) {
        $modelClasses[] = $modelClass;
      }
    }

    return $modelClasses;
  }

  public function getModelClasses() {
    $modelClasses = array();

    foreach ($this->extensions as $extension) {
      $modelClasses = array_merge($modelClasses, $extension->getModelClasses());
    }

    foreach ($this->modelLoaders as $modelLoader) {
      $loadModelClasses = $modelLoader->loadModels();
      foreach ($loadModelClasses as $modelClass) {
        // allow model overrides
        foreach ($modelClasses as $k => $overriddenModel) {
          if (ReflectionHelper::stripClassName($modelClass) == ReflectionHelper::stripClassName($overriddenModel)) {
            unset($modelClasses[$k]);
            break;
          }
        }

        $modelClasses[] = $modelClass;
      }
    }

    return $modelClasses;
  }

  public function addExtension(Extension $extension) {
    $this->extensions[] = $extension;
  }

  /**
   * todo: docs
   *
   * @param $controllerLoaders ControllerLoader[]
   */
  public function setControllerLoaders(array $controllerLoaders) {
    $this->controllerLoaders = $controllerLoaders;
  }

  /**
   * todo: docs
   *
   * @return Controller[]
   */
  public function getControllers() {
    /**
     * @var $controllers Controller[]
     */
    $controllers = array();

    foreach ($this->extensions as $extension) {
      $controllers = array_merge($controllers, $extension->getControllers());
    }

    foreach ($this->controllerLoaders as $controllerLoader) {
      $loadControllers = $controllerLoader->getControllers();
      foreach ($loadControllers as $controller) {
        // allow controller overrides
        foreach ($controllers as $k => $overriddenController) {
          if ($controller->getBase() == $overriddenController->getBase()) {
            unset($controllers[$k]);
            break;
          }
        }

        $controllers[] = $controller;
      }
    }

    return $controllers;
  }

  /**
   * todo: docs
   *
   * @param Parser $parser
   *
   * @return array
   */
  public function getDocJSON(Parser $parser) {
    $docOut = array();
    foreach ($this->getControllers() as $controller) {
      $docOut['endpoints'][] = $controller->getDocJSON($parser);
    }
    return $docOut;
  }

}

