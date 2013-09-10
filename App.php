<?php

namespace MABI;

include_once __DIR__ . '/Slim/Slim.php';
include_once __DIR__ . '/Extension.php';
include_once __DIR__ . '/ErrorResponse.php';

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
   * @return \Slim\Http\Request
   */
  public function getRequest() {
    return $this->slim->request();
  }

  /**
   * @return \Slim\Http\Response
   */
  public function getResponse() {
    return $this->slim->response();
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

  /**
   * todo: docs
   */
  static function clearSingletonApp() {
    self::$singletonApp = NULL;
  }

  public function __construct() {
    if (file_exists(__DIR__ . '/middleware')) {
      array_push($this->middlewareDirectories, __DIR__ . '/middleware');
    }
    $this->slim = new Slim();
    parent::__construct($this);
  }

  /**
   * Returns a JSON array displaying the error to the client and stops execution
   *
   * Example Error Message Definition:
   * define('ERRORDEF_NO_ACCESS', array('message' => 'No Access', 'code' => 1007, 'httpcode' => 402));
   *
   * @param $message string|array|ErrorResponse
   * @param $httpStatusCodeOrReplacementArray int|null|array
   * @param $applicationErrorCode int|null
   *
   * @throws \Slim\Exception\Stop
   * @throws \Exception
   */
  public function returnError($message, $httpStatusCodeOrReplacementArray = NULL, $applicationErrorCode = NULL) {
    $replacementArray = $httpStatusCodeOrReplacementArray;
    if (is_array($message)) {
      $message = ErrorResponse::FromArray($message);
    }
    elseif(is_string($message)) {
      $message = new ErrorResponse($message, $httpStatusCodeOrReplacementArray, $applicationErrorCode);
      $replacementArray = null;
    }
    elseif (!is_subclass_of($message, 'MABI\\ErrorResponse')) {
      throw new \Exception('Invalid type ' . get_class($message) . ' instead of a MABI\ErrorResponse');
    }

    $appCode = $message->getCode();
    echo json_encode(array(
      'error' => empty($appCode) ? array('message' => $message->getFormattedMessage($replacementArray)) :
        array('code' => $appCode, 'message' => $message->getFormattedMessage($replacementArray))
    ));
    $this->getResponse()->status($message->getHttpcode());
    throw new Stop($message->getFormattedMessage($replacementArray));
  }

  public function errorHandler($e) {
    $this->slim->getLog()->error($e);
    $this->getResponse()->status(500);
    echo json_encode(array(
      'error' => array('code' => 1020, 'message' => 'A system error occurred')
    ));
  }

  public function run() {
    foreach ($this->getControllers() as $controller) {
      $controller->loadRoutes($this->slim);
    }

    if (!$this->slim->config('debug')) {
      $this->slim->error(array($this, 'errorHandler'));
    }

    $this->slim->run();
  }

  public function call() {
    foreach ($this->getControllers() as $controller) {
      $controller->loadRoutes($this->slim);
    }

    if (!$this->slim->config('debug')) {
      $this->slim->error(array($this, 'errorHandler'));
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

