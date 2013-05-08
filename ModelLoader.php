<?php

namespace MABI;

include_once __DIR__ . '/Model.php';

/**
 * todo: docs
 */
abstract class ModelLoader {

  /**
   * @return Model[]
   */
  abstract function loadModels();
}
