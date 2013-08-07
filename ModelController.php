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
   * @param $extension Extension
   */
  public function __construct($extension) {
    if (empty($this->modelClass)) {
      $this->modelClass = ReflectionHelper::getPrefixFromControllerClass(get_called_class());
    }

    if (empty($this->base)) {
      $this->base = Inflector::pluralize(strtolower(ReflectionHelper::stripClassName($this->modelClass)));
    }

    parent::__construct($extension);
  }

  public static function generate($modelClass, $extension) {
    $calledClass = get_called_class();
    $newController = new $calledClass($extension);
    $newController->modelClass = $modelClass;
    $newController->base = Inflector::pluralize(strtolower(ReflectionHelper::stripClassName($modelClass)));
    return $newController;
  }

  public function getDocJSON(Parser $parser) {
    $doc = parent::getDocJSON($parser);

    $rClass = new \ReflectionClass(get_called_class());

    if (in_array('show-model', ReflectionHelper::getDocDirective($rClass->getDocComment(), 'docs'))) {
      /**
       * @var $model \MABI\Model
       */
      $model = call_user_func($this->modelClass . '::init', $this->getApp());
      if (empty($doc['models'])) {
        $doc['models'] = array();
      }
      array_unshift($doc['models'], $model->getDocOutput($parser));
    }

    return $doc;
  }


}
