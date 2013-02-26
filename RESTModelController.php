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
    echo json_encode($this->model);
    // todo: implement
  }

  public function _restPutObject($id) {
    // todo: implement
  }

  public function _restDeleteObject($id) {
    // todo: implement
  }

  public function _readModel($id) {
    $this->model = call_user_func($this->modelClass . '::init', $this->app);
    $this->model->findById($id);
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
    $slim->get("/{$this->base}", array($this, '_restGetCollection'));
    $slim->post("/{$this->base}", array($this, '_restPostCollection'));
    $slim->put("/{$this->base}", array($this, '_restPutCollection'));
    $slim->delete("/{$this->base}", array($this, '_restDeleteCollection'));
    $slim->get("/{$this->base}/:id", array($this, '_readModel'), array($this, '_restGetObject'));
    $slim->put("/{$this->base}/:id", array($this, '_readModel'), array($this, '_restPutObject'));
    $slim->delete("/{$this->base}/:id", array($this, '_readModel'), array($this, '_restDeleteObject'));

    /**
     * Gets other automatically generated routes following the pattern:
     * /BASE/:id/ACTION(/:param+) from methods named rest<METHOD><ACTION>()
     * where <METHOD> is GET, PUT, POST, or DELETE
     */
    $rclass = new \ReflectionClass($this);
    $methods = $rclass->getMethods(\ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
      if (strpos($method, 'restGet', 0) === 0) {
        $action = substr($method, 3);
        $slim->get("/{$this->base}/:id/{$action}(/:param+)", array($this, '_readModel'), array($this, $method));
      }
      elseif (strpos($method, 'restPut', 0) === 0) {
        $action = substr($method, 3);
        $slim->put("/{$this->base}/:id/{$action}(/:param+)", array($this, '_readModel'), array($this, $method));
      }
      elseif (strpos($method, 'restPost', 0) === 0) {
        $action = substr($method, 4);
        $slim->post("/{$this->base}/:id/{$action}(/:param+)", array($this, '_readModel'), array($this, $method));
      }
      elseif (strpos($method, 'restDelete', 0) === 0) {
        $action = substr($method, 6);
        $slim->delete("/{$this->base}/:id/{$action}(/:param+)", array($this, '_readModel'), array($this, $method));
      }
    }
  }
}
