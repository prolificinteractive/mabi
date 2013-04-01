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
   * @var \MABI\Middleware[]
   */
  protected $middlewares = array();

  /**
   * @return \MABI\App
   */
  public function getApp() {
    return $this->app;
  }

  public function getMiddlewares() {
    return $this->middlewares;
  }

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
   * @param $middlewares  \MABI\Middleware[]
   */
  protected function configureMiddlewares(&$middlewares) {
    /**
     * @var $prevMiddelware \MABI\Middleware
     */
    $prevMiddelware = NULL;
    foreach ($middlewares as $currMiddelware) {
      if ($prevMiddelware != NULL) {
        $prevMiddelware->setNextMiddleware($currMiddelware);
      }
      $prevMiddelware = $currMiddelware;
      $currMiddelware->setController($this);
    }
  }

  public function addMiddleware(\Slim\Middleware $newMiddleware) {
    array_unshift($this->middlewares, $newMiddleware);
  }

  /**
   * @param $slim \Slim\Slim
   */
  public function loadRoutes($slim) {
    $this->configureMiddlewares($this->middlewares);

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
