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

  /**
   * @var \Slim\Middleware[]
   */
  protected $middlewares = array();

  /**
   * @var \Slim\Http\Request
   */
  protected $request;

  public function __construct($app) {
    $this->app = $app;

    if (empty($this->base)) {
      $this->base = strtolower(ReflectionHelper::stripClassName(
        ReflectionHelper::getPrefixFromControllerClass(get_called_class())));
    }
  }

  /**
   * @param $route \Slim\Route
   */
  public function _runControllerMiddlewares($route) {
    if (empty($this->middlewares)) {
      return;
    }
    $middleware = reset($this->middlewares);
    $middleware->call();
  }

  /**
   * @param $slim \Slim\Slim
   * @param $middlewares  \Slim\Middleware[]
   */
  protected static function configureMiddlewares($slim, &$middlewares) {
    /**
     * @var $prevMiddelware \Slim\Middleware
     */
    $prevMiddelware = NULL;
    foreach ($middlewares as $currMiddelware) {
      if ($prevMiddelware != NULL) {
        $prevMiddelware->setNextMiddleware($currMiddelware);
      }
      $prevMiddelware = $currMiddelware;
      $currMiddelware->setApplication($slim);
    }
  }

  public function addMiddleware(\Slim\Middleware $newMiddleware)
  {
    array_unshift($this->middlewares, $newMiddleware);
  }

  /**
   * @param $slim \Slim\Slim
   */
  public function loadRoutes($slim) {
    self::configureMiddlewares($slim, $this->middlewares);

    $this->request = $slim->request();

    $rclass = new \ReflectionClass($this);
    $methods = $rclass->getMethods(\ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
      $methodName = $method->name;
      if (strpos($methodName, 'get', 0) === 0) {
        $action = strtolower(substr($methodName, 3));
        $slim->get("/{$this->base}/{$action}", array($this, '_runControllerMiddlewares'), array($this, $methodName));
        $slim->get("/{$this->base}/{$action}(/:param+)", array(
          $this,
          '_runControllerMiddlewares'
        ), array(
          $this,
          $methodName
        ));
      }
      elseif (strpos($methodName, 'put', 0) === 0) {
        $action = strtolower(substr($methodName, 3));
        $slim->put("/{$this->base}/{$action}", array($this, '_runControllerMiddlewares'), array($this, $methodName));
        $slim->put("/{$this->base}/{$action}(/:param+)", array(
          $this,
          '_runControllerMiddlewares'
        ), array(
          $this,
          $methodName
        ));
      }
      elseif (strpos($methodName, 'post', 0) === 0) {
        $action = strtolower(substr($methodName, 4));
        $slim->post("/{$this->base}/{$action}", array($this, '_runControllerMiddlewares'), array($this, $methodName));
        $slim->post("/{$this->base}/{$action}(/:param+)", array(
          $this,
          '_runControllerMiddlewares'
        ), array(
          $this,
          $methodName
        ));
      }
      elseif (strpos($methodName, 'delete', 0) === 0) {
        $action = strtolower(substr($methodName, 6));
        $slim->delete("/{$this->base}/{$action}", array($this, '_runControllerMiddlewares'), array($this, $methodName));
        $slim->delete("/{$this->base}/{$action}(/:param+)", array(
          $this,
          '_runControllerMiddlewares'
        ), array(
          $this,
          $methodName
        ));
      }
    }
  }
}
