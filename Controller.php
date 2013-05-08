<?php

namespace MABI;

include_once __DIR__ . '/Inflector.php';
include_once __DIR__ . '/Middleware.php';

/**
 * Defines a controller that serves endpoints routed based on its contained function names.
 *
 * The controller also allows middleware to be associated with all of the endpoints that it serves.
 */
class Controller {
  /**
   * The base name for the controller. This defines the first part of the endpoint url.
   *
   * e.g. <APP_PATH>/{BASE}/{ACTION}/...
   *
   * @var string
   */
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
   * @endpoint ignore
   * @return \MABI\App
   */
  public function getApp() {
    return $this->app;
  }

  /**
   * @endpoint ignore
   * @return array|Middleware[]
   */
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

    // Load middlewares from @middleware directive
    $middlewares = ReflectionHelper::getDocDirective($rClass->getDocComment(), 'middleware');
    foreach ($middlewares as $middlewareClass) {
      $this->addMiddlewareByClass($middlewareClass);
    }
  }

  public function addMiddlewareByClass($middlewareClass) {
    $middlewareFile = ReflectionHelper::stripClassName($middlewareClass) . '.php';
    // Finds the file to include for this middleware using the app's middleware directory listing
    foreach ($this->app->getMiddlewareDirectories() as $middlewareDirectory) {
      if (file_exists($middlewareDirectory . '/' . $middlewareFile)) {
        include_once $middlewareDirectory . '/' . $middlewareFile;
        break;
      }
    }

    /**
     * @var $middleware \MABI\Middleware
     */
    $middleware = new $middlewareClass();
    $this->addMiddleware($middleware);
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

    $rClass = new \ReflectionClass($this);
    $rMethods = $rClass->getMethods(\ReflectionMethod::IS_PUBLIC);
    foreach ($rMethods as $rMethod) {
      // If there is a '@endpoint ignore' property, the function is not served as an endpoint
      if (in_array('ignore', ReflectionHelper::getDocDirective($rMethod->getDocComment(), 'endpoint'))) {
        continue;
      }

      $methodName = $rMethod->name;
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

  /**
   * todo: docs
   *
   * @param Parser $parser
   *
   * @endpoint ignore
   * @return array
   */
  public function getDocJSON(Parser $parser) {
    $myClass = get_called_class();
    $rClass = new \ReflectionClass($myClass);

    $doc = array();
    $doc['name'] = ucwords(ReflectionHelper::stripClassName(ReflectionHelper::getPrefixFromControllerClass($myClass)));
    $doc['description'] = $parser->parse(ReflectionHelper::getDocText($rClass->getDocComment()));

    // Adding documentation for custom controller actions
    $rMethods = $rClass->getMethods(\ReflectionMethod::IS_PUBLIC);
    foreach ($rMethods as $rMethod) {
      // If there is a '@endpoint ignore' property, the function is not served as an endpoint
      if (in_array('ignore', ReflectionHelper::getDocDirective($rMethod->getDocComment(), 'endpoint'))) {
        continue;
      }

      $methodDoc = array();

      if (strpos($rMethod->name, 'get', 0) === 0) {
        $methodDoc['MethodName'] = substr($rMethod->name, 3);
        $methodDoc['HTTPMethod'] = 'GET';
      }
      elseif (strpos($rMethod->name, 'put', 0) === 0) {
        $methodDoc['MethodName'] = substr($rMethod->name, 3);
        $methodDoc['HTTPMethod'] = 'PUT';
      }
      elseif (strpos($rMethod->name, 'post', 0) === 0) {
        $methodDoc['MethodName'] = substr($rMethod->name, 4);
        $methodDoc['HTTPMethod'] = 'POST';
      }
      elseif (strpos($rMethod->name, 'delete', 0) === 0) {
        $methodDoc['MethodName'] = substr($rMethod->name, 6);
        $methodDoc['HTTPMethod'] = 'DELETE';
      }
      else {
        continue;
      }
      $action = strtolower($methodDoc['MethodName']);
      $methodDoc['URI'] = "/{$this->base}/{$action}";
      $methodDoc['Synopsis'] = $parser->parse(ReflectionHelper::getDocText($rMethod->getDocComment()));
      $methodDoc['parameters'] = array();

      // Allow controller middlewares to modify the documentation for this method
      if (!empty($this->middlewares)) {
        $middleware = reset($this->middlewares);
        $middleware->documentMethod($rClass, $rMethod, $methodDoc);
      }

      if (!empty($methodDoc)) {
        $doc['methods'][] = $methodDoc;
      }
    }

    return $doc;
  }
}