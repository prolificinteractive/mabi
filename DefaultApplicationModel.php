<?php

namespace MABI;

/**
 * @restful NoController
 */
class Application extends Model {
  /**
   * @var string
   */
  public $applicationName;

  /**
   * @var string
   */
  public $sharedSecret;
}
