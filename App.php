<?php

namespace MABI;

include_once __DIR__ . '/Slim/Slim.php';
include_once __DIR__ . '/Extension.php';
include_once __DIR__ . '/ErrorResponse.php';
include_once __DIR__ . '/DefaultAppErrors.php';

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
   * @var ErrorResponseDictionary
   */
  protected $errorResponseDictionary = NULL;

  /**
   * @return \MABI\ErrorResponseDictionary
   */
  public function getErrorResponseDictionary() {
    return $this->errorResponseDictionary;
  }

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
    $this->errorResponseDictionary = new DefaultAppErrors();
    parent::__construct($this);
  }

  /**
   * Returns a JSON array displaying the error to the client and stops execution
   *
   * Example Error Message Definition:
   * array('ERRORDEF_NO_ACCESS' => array('message' => 'No Access', 'code' => 1007, 'httpcode' => 402));
   *
   * @param $errorKeyOrDefinition string|array
   * @param $replacementArray array
   *
   * @throws \Slim\Exception\Stop
   */
  public function returnError($errorKeyOrDefinition, $replacementArray = array()) {
    if (is_string($errorKeyOrDefinition)) {
      $errorKey = $errorKeyOrDefinition;
    }
    else {
      $errorKey = array_keys($errorKeyOrDefinition)[0];
    }

    $errorResponse = $this->errorResponseDictionary->getErrorResponse($errorKey);
    if (empty($errorResponse)) {
      $errorResponse = ErrorResponse::FromArray($errorKeyOrDefinition[$errorKey]);
    }

    $appCode = $errorResponse->getCode();
    echo json_encode(array(
      'error' => empty($appCode) ? array('message' => $errorResponse->getFormattedMessage($replacementArray)) :
          array('code' => $appCode, 'message' => $errorResponse->getFormattedMessage($replacementArray))
    ));
    $this->getResponse()->status($errorResponse->getHttpcode());
    throw new Stop($errorResponse->getFormattedMessage($replacementArray));
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

