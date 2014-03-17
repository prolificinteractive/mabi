<?php

namespace mabiTesting;

class ModelB extends \MABI\Model {
  protected $idProperty = 'modelBId';
  public $name;
  /**
   * @var string
   * @Field\owner
   */
  public $testOwner;
}
