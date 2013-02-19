<?php

namespace MABI;

include_once dirname(__FILE__) . '/Inflector.php';
include_once dirname(__FILE__) . '/Utilities.php';

/**
 * todo: docs
 */
class RESTModelController extends Controller {
  protected $base = NULL;
  protected $modelClass = NULL;

  /**
   * @param $modelClass string
   * @param $app App
   */
  public function __construct($modelClass, $app) {
    parent::__construct($app);

    $this->modelClass = $modelClass;
    if (empty($this->base)) {
      $controllerClass = get_called_class();
    }
  }

  protected function getCollection() {
    /**
     * @var $model Model
     */
    $model = call_user_func($this->modelClass . '::init', $this->app);
    $model->getAll();
  }

  protected function postCollection() {
    /**
     * @var $model Model
     */
    $model = call_user_func($this->modelClass . '::init', $this->app);
    // todo: get post data to insert
    $model->insert();
  }

  protected function putCollection() {
    // todo: implement
  }

  protected function deleteCollection() {
    // todo: implement
  }

  protected function getObject($id) {
    // todo: implement
  }

  protected function putObject($id) {
    // todo: implement
  }

  protected function deleteObject($id) {
    // todo: implement
  }

  /**
   * @param $slim \Slim\Slim
   */
  public function loadRoutes($slim) {
    /*
     * GET      /<model>          get all models by id
     * POST     /<model>          creates a new model
     * PUT      /<model>          bulk creates full new model collection
     * DELETE   /<model>          deletes all models
     * GET      /<model>/<id>     gets one model's full details
     * PUT      /<model>/<id>     updates the model
     * DELETE   /<model>/<id>     deletes the model
     */

    $slim->get("/{$this->base}", $this->deleteObject);
    $slim->post("/{$this->base}", $this->postCollection);
    $slim->put("/{$this->base}", $this->putCollection);
    $slim->delete("/{$this->base}", $this->deleteCollection);
    $slim->get("/{$this->base}/:id", $this->getObject);
    $slim->put("/{$this->base}/:id", $this->putObject);
    $slim->delete("/{$this->base}/:id", $this->deleteObject);
  }
}
