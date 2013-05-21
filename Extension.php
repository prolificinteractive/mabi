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
   * @var string[]
   */
  protected $modelClasses = array();

  /**
   * @var Array
   */
  protected $config = array();

  /**
   * @var Extension[]
   */
  protected $extensions = array();

  /**
   * @var Controller[]
   */
  protected $controllers = array();

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

  public function __construct() {
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
    $this->modelClasses = array();

    foreach ($this->extensions as $extension) {
      foreach ($extension->modelClasses as $modelClass) {
        $this->modelClasses[] = $modelClass;
      }
    }

    foreach ($modelLoaders as $modelLoader) {
      $modelClasses = $modelLoader->loadModels();
      foreach ($modelClasses as $modelClass) {
        // todo: allow model overrides
        $this->modelClasses[] = $modelClass;
      }
    }
  }

  public function getDataConnection($name) {
    $extensionDataConnections = array();
    foreach ($this->extensions as $extension) {
      $extensionDataConnections = array_merge($extensionDataConnections, $extension->dataConnections);
    }

    $dataConnections = array_merge($extensionDataConnections, $this->dataConnections);
    return $dataConnections[$name];
  }

  public function getModelClasses() {
    return $this->modelClasses;
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
    $this->controllers = array();

    foreach ($this->extensions as $extension) {
      foreach ($extension->controllers as $controller) {
        $this->controllers[] = $controller;
      }
    }

    foreach ($controllerLoaders as $controllerLoader) {
      $controllers = $controllerLoader->getControllers();
      foreach ($controllers as $controller) {
        // todo: allow controller overrides
        $this->controllers[] = $controller;
      }
    }
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
    foreach ($this->controllers as $controller) {
      $docOut['endpoints'][] = $controller->getDocJSON($parser);
    }
    return $docOut;
  }

}

