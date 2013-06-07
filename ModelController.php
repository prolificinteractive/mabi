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
    if (empty($this->modelClass)) {
      $this->modelClass = ReflectionHelper::getPrefixFromControllerClass(get_called_class());
    }

    if (empty($this->base)) {
      $this->base = Inflector::pluralize(strtolower(ReflectionHelper::stripClassName($this->modelClass)));
    }

    parent::__construct($app);
  }

  public static function generate($modelClass, $app) {
    $calledClass = get_called_class();
    $newController = new $calledClass($app);
    $newController->modelClass = $modelClass;
    $newController->base = Inflector::pluralize(strtolower(ReflectionHelper::stripClassName($modelClass)));
    return $newController;
  }
}
