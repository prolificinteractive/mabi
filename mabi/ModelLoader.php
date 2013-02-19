<?php

namespace MABI;

include_once dirname(__FILE__) . '/Model.php';

/**
 * todo: docs
 */
abstract class ModelLoader {

  /**
   * @return Model[]
   */
  abstract function loadModels();
}
