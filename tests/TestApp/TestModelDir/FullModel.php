<?php

namespace mabiTesting;

class FullModel extends \MABI\Model {
  /**
   * @var string
   */
  public $init_id;

  /**
   * @var int
   */
  public $intField;

  /**
   * @var bool
   */
  public $boolField;

  /**
   * @var float
   */
  public $floatField;

  /**
   * @var \DateTime
   */
  public $timestampField;

  /**
   * @var array
   */
  public $arrayField;

  /**
   * @var \mabiTesting\ModelB[]
   */
  public $subObjList;

  /**
   * @var string
   * @Field\internal
   */
  public $internalField;

  /**
   * @var string
   * @Field\external
   */
  public $externalField;

  /**
   * @var string
   * @Field\system
   */
  public $systemField;
}