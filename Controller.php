<?php

namespace MABI;

include_once dirname(__FILE__) . '/Inflector.php';

/**
 * todo: docs
 */
abstract class Controller {

  /**
   * @var App
   */
  protected $app;

  public function __construct($app) {
    $this->app = $app;
  }

  /**
   * @param $slim \Slim\Slim
   */
  abstract function loadRoutes($slim);
}
