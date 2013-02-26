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
      if (strpos($method, 'get', 0) === 0) {
        $action = substr($method,3);
        $slim->get("/{$this->base}/{$action}(/:param+)", array($this, $method));
      }
      elseif (strpos($method, 'put', 0) === 0) {
        $action = substr($method,3);
        $slim->put("/{$this->base}/{$action}(/:param+)", array($this, $method));
      }
      elseif (strpos($method, 'post', 0) === 0) {
        $action = substr($method,4);
        $slim->post("/{$this->base}/{$action}(/:param+)", array($this, $method));
      }
      elseif (strpos($method, 'delete', 0) === 0) {
        $action = substr($method,6);
        $slim->delete("/{$this->base}/{$action}(/:param+)", array($this, $method));
      }
    }
  }
}
