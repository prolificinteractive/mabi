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
    foreach ($this->getControllers() as $controller) {
      $controller->loadRoutes($this->slim);
    }

    $this->slim->run();
  }

  public function call() {
    foreach ($this->getControllers() as $controller) {
      $controller->loadRoutes($this->slim);
    }

    $this->slim->call();
  }

  public function getIOSModel() {
    $iosModel = new \SimpleXMLElement('<model/>');
    $iosModel->addAttribute('name', '');
    $iosModel->addAttribute('userDefinedModelVersionIdentifier', '');
    $iosModel->addAttribute('type', 'com.apple.IDECoreDataModeler.DataModel');
    $iosModel->addAttribute('documentVersion', '1.0');
    $iosModel->addAttribute('lastSavedToolsVersion', '2061');
    $iosModel->addAttribute('systemVersion', '12D78');
    $iosModel->addAttribute('minimumToolsVersion', 'Xcode 4.3');
    $iosModel->addAttribute('macOSVersion', 'Automatic');
    $iosModel->addAttribute('iOSVersion', 'Automatic');

    foreach($this->modelClasses as $modelClass) {
      $model = call_user_func($modelClass . '::init', $this);
      $model->getIOSModel($iosModel);
    }

    return $iosModel->asXML();
  }
}

