<?php

namespace MABI;

include_once dirname(__FILE__) . '/Inflector.php';
include_once dirname(__FILE__) . '/Utilities.php';
include_once dirname(__FILE__) . '/ModelController.php';

/**
 * todo: docs
 */
class RESTModelController extends ModelController {
  /**
   * @param $app App
   */
  public function __construct($app) {
    parent::__construct($app);
  }

  /**
   * @var \Mabi\Model
   */
  protected $model = NULL;

  public function _restGetCollection() {
    /**
     * @var $model Model
     */
    $model = call_user_func($this->modelClass . '::init', $this->app);
    echo json_encode($model->findAll());
  }

  public function _restPostCollection() {
    // todo: get post data to insert

    $this->model = call_user_func($this->modelClass . '::init', $this->app);
    $this->model->loadParameters($this->getApp()->getSlim()->request()->post());
    $this->model->insert();
    echo $this->model->outputJSON();
  }

  public function _restPutCollection() {
    // todo: implement
  }

  public function _restDeleteCollection() {
    // todo: implement
  }

  public function _restGetObject($id) {
    /**
     * @var $model Model
     */
    echo $this->model->outputJSON();
    // todo: implement
  }

  public function _restPutObject($id) {
    // todo: implement
  }

  public function _restDeleteObject($id) {
    // todo: implement
  }

  /**
   * @param $route \Slim\Route
   */
  public function _readModel($route) {
    $this->model = call_user_func($this->modelClass . '::init', $this->app);
    $this->model->findById($route->getParam('id'));
  }

  /**
   * @param $slim \Slim\Slim
   */
  public function loadRoutes($slim) {
    parent::loadRoutes($slim);

    /**
     * Automatically generates routes for the following
     *
     * GET      /<model>          get all models by id
     * POST     /<model>          creates a new model
     * PUT      /<model>          bulk creates full new model collection
     * DELETE   /<model>          deletes all models
     * GET      /<model>/<id>     gets one model's full details
     * PUT      /<model>/<id>     updates the model
     * DELETE   /<model>/<id>     deletes the model
     */

    // todo: add API versioning
    $slim->get("/{$this->base}",
      array($this, '_runControllerMiddlewares'),
      array($this, '_restGetCollection'));
    $slim->post("/{$this->base}",
      array($this, '_runControllerMiddlewares'),
      array($this, '_restPostCollection'));
    $slim->put("/{$this->base}",
      array($this, '_runControllerMiddlewares'),
      array($this, '_restPutCollection'));
    $slim->delete("/{$this->base}",
      array($this, '_runControllerMiddlewares'),
      array($this, '_restDeleteCollection'));
    $slim->get("/{$this->base}/:id",
      array($this, '_runControllerMiddlewares'),
      array($this, '_readModel'),
      array($this, '_restGetObject'));
    $slim->put("/{$this->base}/:id",
      array($this, '_runControllerMiddlewares'),
      array($this, '_readModel'),
      array($this, '_restPutObject'));
    $slim->delete("/{$this->base}/:id",
      array($this, '_runControllerMiddlewares'),
      array($this, '_readModel'),
      array($this, '_restDeleteObject'));

    /**
     * Gets other automatically generated routes following the pattern:
     * /BASE/:id/ACTION(/:param+) from methods named rest<METHOD><ACTION>()
     * where <METHOD> is GET, PUT, POST, or DELETE
     */
    $rclass = new \ReflectionClass($this);
    $methods = $rclass->getMethods(\ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
      $methodName = $method->name;
      if (strpos($methodName, 'restGet', 0) === 0) {
        $action = strtolower(substr($methodName, 7));
        $slim->get("/{$this->base}/:id/{$action}",
          array($this, '_runControllerMiddlewares'),
          array($this, '_readModel'),
          array($this, $methodName));
        $slim->get("/{$this->base}/:id/{$action}(/:param)",
          array($this, '_runControllerMiddlewares'),
          array($this, '_readModel'),
          array($this, $methodName));
      }
      elseif (strpos($methodName, 'restPut', 0) === 0) {
        $action = strtolower(substr($methodName, 7));
        $slim->put("/{$this->base}/:id/{$action}",
          array($this, '_runControllerMiddlewares'),
          array($this, '_readModel'),
          array($this, $methodName));
        $slim->put("/{$this->base}/:id/{$action}(/:param)",
          array($this, '_runControllerMiddlewares'),
          array($this, '_readModel'),
          array($this, $methodName));
      }
      elseif (strpos($methodName, 'restPost', 0) === 0) {
        $action = strtolower(substr($methodName, 8));
        $slim->post("/{$this->base}/:id/{$action}",
          array($this, '_runControllerMiddlewares'),
          array($this, '_readModel'),
          array($this, $methodName));
        $slim->post("/{$this->base}/:id/{$action}(/:param)",
          array($this, '_runControllerMiddlewares'),
          array($this, '_readModel'),
          array($this, $methodName));
      }
      elseif (strpos($methodName, 'restDelete', 0) === 0) {
        $action = strtolower(substr($methodName, 10));
        $slim->delete("/{$this->base}/:id/{$action}",
          array($this, '_runControllerMiddlewares'),
          array($this, '_readModel'),
          array($this, $methodName));
        $slim->delete("/{$this->base}/:id/{$action}(/:param)",
          array($this, '_runControllerMiddlewares'),
          array($this, '_readModel'),
          array($this, $methodName));
      }
    }
  }
}
