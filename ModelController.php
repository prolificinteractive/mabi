<?php

namespace MABI;

include_once dirname(__FILE__) . '/Utilities.php';
include_once dirname(__FILE__) . '/Controller.php';

/**
 * todo: docs
 */
abstract class ModelController extends Controller {
  protected $modelClass = NULL;

  public function getModelClass() {
    return $this->modelClass;
  }

  /**
   * @param $app App
   */
  public function __construct($app) {
    parent::__construct($app);

    if (empty($this->modelClass)) {
      $this->modelClass = ReflectionHelper::getModelClassFromController(get_called_class());
    }
  }
}
