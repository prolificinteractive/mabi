<?php

namespace MABI;

include_once __DIR__ . '/Utilities.php';
include_once __DIR__ . '/Controller.php';

/**
 * todo: docs
 */
class ModelController extends Controller {
  protected $modelClass = NULL;

  /**
   * @endpoint ignore
   * @return string
   */
  public function getModelClass() {
    return $this->modelClass;
  }

  /**
   * @param $app App
   */
  public function __construct($app) {
    parent::__construct($app);

    if (empty($this->modelClass)) {
      $this->modelClass = ReflectionHelper::getPrefixFromControllerClass(get_called_class());
    }

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
}
