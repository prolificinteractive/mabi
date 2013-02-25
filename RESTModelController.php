<?php

namespace MABI;

include_once dirname(__FILE__) . '/Inflector.php';
include_once dirname(__FILE__) . '/Utilities.php';
include_once dirname(__FILE__) . '/ModelController.php';

/**
 * todo: docs
 */
class RESTModelController extends ModelController {
  protected $base = NULL;

  /**
   * @param $app App
   */
  public function __construct($app) {
    parent::__construct($app);

    if (empty($this->base)) {
      $this->base = strtolower(ReflectionHelper::stripClassName($this->modelClass));
    }
  }

  public static function generate($modelClass, $app) {
    $newController = new RESTModelController($app);
    $newController->modelClass = $modelClass;
    $newController->base = strtolower(ReflectionHelper::stripClassName($modelClass));
    return $newController;
  }

  public function getCollection() {
    /**
     * @var $model Model
     */
    $model = call_user_func($this->modelClass . '::init', $this->app);
    return $model->findAll();
  }

  public function postCollection() {
    // todo: get post data to insert
  }

  public function putCollection() {
    // todo: implement
  }

  public function deleteCollection() {
    // todo: implement
  }

  public function getObject($id) {
    /**
     * @var $model Model
     */
    $model = call_user_func($this->modelClass . '::init', $this->app);
    $model->findById($id);
    // todo: implement
  }

  public function putObject($id) {
    // todo: implement
  }

  public function deleteObject($id) {
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

    // todo: add API versioning

    $slim->get("/{$this->base}", array($this, 'getCollection'));
    $slim->post("/{$this->base}", array($this, 'postCollection'));
    $slim->put("/{$this->base}", array($this, 'putCollection'));
    $slim->delete("/{$this->base}", array($this, 'deleteCollection'));
    $slim->get("/{$this->base}/:id", array($this, 'getObject'));
    $slim->put("/{$this->base}/:id", array($this, 'putObject'));
    $slim->delete("/{$this->base}/:id", array($this, 'deleteObject'));
  }
}
