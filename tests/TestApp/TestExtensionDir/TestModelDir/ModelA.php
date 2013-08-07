<?php

namespace mabiTesting\testExtension;

class ModelA extends \MABI\Model {
  /**
   * @var string
   */
  public $init_id;

  /**
   * @var \mabiTesting\ModelB
   */
  public $partner;
}