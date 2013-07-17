<?php

namespace MABI;

include_once dirname(__FILE__) . '/Inflector.php';
include_once dirname(__FILE__) . '/Middleware.php';

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

    $myClass = get_called_class();

    if (empty($this->base)) {
      $this->base = strtolower(ReflectionHelper::stripClassName(
        ReflectionHelper::getPrefixFromControllerClass($myClass)));
    }

    $rClass = new \ReflectionClass($myClass);
    $middlewares = ReflectionHelper::getDocProperty($rClass->getDocComment(), 'middleware');
    foreach ($middlewares as $middlewareClass) {
      $middlewareFile = ReflectionHelper::stripClassName($middlewareClass) . '.php';
      include_once dirname(__FILE__) . '/middleware/' . $middlewareFile;

      /**
       * @var $middleware \MABI\Middleware
       */
      $middleware = new $middlewareClass();
      $this->addMiddleware($middleware);
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
     * @var $prevMiddleware \MABI\Middleware
     */
    $prevMiddleware = NULL;
    foreach ($middlewares as $currMiddleware) {
      if ($prevMiddleware != NULL) {
        $prevMiddleware->setNextMiddleware($currMiddleware);
      }
      $prevMiddleware = $currMiddleware;
      $currMiddleware->setController($this);
    }
  }

  public function addMiddleware(Middleware $newMiddleware) {
    array_push($this->middlewares, $newMiddleware);
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
