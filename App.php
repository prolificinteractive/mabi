<?php

namespace MABI;

include_once __DIR__ . '/Slim/Slim.php';
include_once __DIR__ . '/Extension.php';

use \Slim\Slim;
use Slim\Exception\Stop;

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
  static function getSingletonApp() {
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
    $this->slim->error(array($this, 'errorHandler'));
    parent::__construct($this);
  }

  /**
   * Returns a JSON array displaying the error to the client and stops execution
   *
   * @param $message string
   * @param $httpStatusCode int
   * @param $applicationErrorCode int
   *
   * @throws \Slim\Exception\Stop
   */
  public function returnError($message, $httpStatusCode, $applicationErrorCode = NULL) {
    echo json_encode(array(
      'error' => empty($applicationErrorCode) ? array('message' => $message) :
        array('code' => $applicationErrorCode, 'message' => $message)
    ));
    $this->getApp()->getSlim()->response()->status($httpStatusCode);
    throw new Stop($message);
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
    $iosModel = IOSModelInterpreter::getIOSDataModel();

    foreach ($this->getModelClasses() as $modelClass) {
      $model = call_user_func($modelClass . '::init', $this);
      IOSModelInterpreter::addModel($iosModel, $model);
    }

    return $iosModel->asXML();
  }
}

