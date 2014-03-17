<?php

namespace MABI;

include_once __DIR__ . '/Model.php';

/**
 * @Model\NoController
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
