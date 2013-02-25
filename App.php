<?php

namespace MABI;

include_once dirname(__FILE__) . '/DataConnection.php';
include_once dirname(__FILE__) . '/ModelLoader.php';
include_once dirname(__FILE__) . '/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

/**
 * todo: docs
 */
class App {

  /**
   * @var DataConnection[]
   */
  protected $dataConnections;

  /**
   * @var ModelLoader[]
   */
  protected $modelLoaders;

  /**
   * @var string[]
   */
  protected $modelClasses;

  /**
   * @var \Slim\Slim;
   */
  protected $slim;

  /**
   * @var App
   */
  protected static $singletonApp = NULL;

  /**
   * todo: docs
   */
  static function getApp() {
    if (empty(self::$singletonApp)) {
      self::$singletonApp = new App();
    }

    return self::$singletonApp;
  }

  public function __construct() {
    $this->slim = new \Slim\Slim();
  }

  /**
   * todo: docs
   *
   * @param $name string
   * @param $dataConnection DataConnection
   */
  function addDataConnection($name, $dataConnection) {
    $this->dataConnections[$name] = $dataConnection;
    // todo: implement
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
  public function setModelLoaders($modelLoaders) {
    $this->modelLoaders = $modelLoaders;

    $this->modelClasses = array();
    foreach ($modelLoaders as $modelLoader) {
      $modelClasses = $modelLoader->loadModels();
      foreach ($modelClasses as $modelClass) {
        $this->modelClasses[] = $modelClass;
      }
    }
  }

  public function getDataConnection($name) {
    return $this->dataConnections[$name];
  }

  public function getModelClasses() {
    return $this->modelClasses;
  }

  /**
   * todo: docs
   *
   * @param $controllerLoaders ControllerLoader[]
   */
  public function setControllerLoaders($controllerLoaders) {
    foreach ($controllerLoaders as $controllerLoader) {
      $controllers = $controllerLoader->getControllers();
      foreach ($controllers as $controller) {
        $controller->loadRoutes($this->slim);
      }
    }
  }

  public function run() {
    $this->slim->run();
  }
}

