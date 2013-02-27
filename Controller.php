<?php

namespace MABI;

include_once dirname(__FILE__) . '/Inflector.php';

/**
 * todo: docs
 */
class Controller {

  protected $base = NULL;

  /**
   * @var App
   */
  protected $app;

  public function __construct($app) {
    $this->app = $app;

    if (empty($this->base)) {
      $this->base = strtolower(ReflectionHelper::stripClassName(
        ReflectionHelper::getPrefixFromControllerClass(get_called_class())));
    }
  }

  /**
   * @param $slim \Slim\Slim
   */
  public function loadRoutes($slim) {
    $rclass = new \ReflectionClass($this);
    $methods = $rclass->getMethods(\ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
      $methodName = $method->name;
      if (strpos($methodName, 'get', 0) === 0) {
        $action = strtolower(substr($methodName,3));
        $slim->get("/{$this->base}/{$action}", array($this, $methodName));
      }
      elseif (strpos($methodName, 'put', 0) === 0) {
        $action = strtolower(substr($methodName,3));
        $slim->put("/{$this->base}/{$action}", array($this, $methodName));
      }
      elseif (strpos($methodName, 'post', 0) === 0) {
        $action = strtolower(substr($methodName,4));
        $slim->post("/{$this->base}/{$action}", array($this, $methodName));
      }
      elseif (strpos($methodName, 'delete', 0) === 0) {
        $action = strtolower(substr($methodName,6));
        $slim->delete("/{$this->base}/{$action}", array($this, $methodName));
      }
    }
  }
}
