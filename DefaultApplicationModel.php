<?php

namespace MABI;

/**
 * @restful NoController
 */
class DefaultApplicationModel extends Model {
  protected $table = 'applications';

  /**
   * @var string
   */
  public $applicationName;

  /**
   * @var string
   */
  public $sharedSecret;
}
