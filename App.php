<?php

namespace MABI;

include_once dirname(__FILE__) . '/DataConnection.php';
include_once dirname(__FILE__) . '/ModelLoader.php';
include_once dirname(__FILE__) . '/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

interface Parser {
  function Parse($text);
}

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
   * @var Array
   */
  protected $config = array();

  /**
   * @var Controller[]
   */
  protected $controllers = array();

  /**
   * @var string[]
   */
  protected $middlewareDirectories = array();

  /**
   * @return \Slim\Slim
   */
  public function getSlim() {
    return $this->slim;
  }

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
    return $this->middlewareDirectories;
  }

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
    array_push($this->middlewareDirectories, dirname(__FILE__) . '/middleware');
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
    return $this->config[$key];
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
        $this->controllers[] = $controller;
      }
    }
  }

  public function run() {
    $this->slim->run();
  }

  /**
   * todo: docs
   *
   * @param Parser $parser
   */
  public function getDocJSON(Parser $parser) {
    $docOut = array();
    foreach ($this->controllers as $controller) {
      $docOut['endpoints'][] = $controller->getDocJSON($parser);
    }
    return $docOut;
  }
}

