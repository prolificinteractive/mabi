<?php

namespace MABI;

include_once __DIR__ . '/Slim/Slim.php';
include_once __DIR__ . '/Extension.php';

use \Slim\Slim;

Slim::registerAutoloader();

/**
 * todo: docs
 */
class App extends Extension {

  /**
   * @var \Slim\Slim;
   */
  protected $slim;

  /**
   * @return \Slim\Slim
   */
  public function getSlim() {
    return $this->slim;
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
    if (file_exists(__DIR__ . '/middleware')) {
      array_push($this->middlewareDirectories, __DIR__ . '/middleware');
    }
    $this->slim = new Slim();
  }

  public function run() {
    foreach ($this->controllers as $controller) {
      $controller->loadRoutes($this->slim);
    }

    $this->slim->run();
  }

  public function call() {
    foreach ($this->controllers as $controller) {
      $controller->loadRoutes($this->slim);
    }

    $this->slim->call();
  }
}

