<?php

namespace MABI;

include_once __DIR__ . '/App.php';
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

  protected $documentationName = NULL;

  /**
   * @endpoint ignore
   * @return string
   */
  public function getBase() {
    return $this->base;
  }

  /**
   * @var Extension
   */
  protected $extension;

  /**
   * @var \MABI\Middleware[]
   */
  protected $middlewares = array();

  /**
   * @endpoint ignore
   * @return \MABI\App
   */
  public function getApp() {
    return $this->extension->getApp();
  }

  /**
   * @endpoint ignore
   * @return \MABI\Extension
   */
  public function getExtension() {
    return $this->extension;
  }

  /**
   * @endpoint ignore
   * @return array|Middleware[]
   */
  public function getMiddlewares() {
    return $this->middlewares;
  }

  public function __construct($extension) {
    $this->extension = $extension;

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

    if (empty($this->documentationName)) {
      $this->documentationName = ucwords(ReflectionHelper::stripClassName(ReflectionHelper::getPrefixFromControllerClass($myClass)));
    }
  }

  public function addMiddlewareByClass($middlewareClass) {
    $middlewareFile = ReflectionHelper::stripClassName($middlewareClass) . '.php';
    // Finds the file to include for this middleware using the app's middleware directory listing
    foreach ($this->extension->getMiddlewareDirectories() as $middlewareDirectory) {
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
   * An overridable function that is called before a route executes middleware
   */
  public function preMiddleware() {
  }

  /**
   * An overridable function that is called before a route executes the final callable (after middleware)
   */
  public function preCallable() {
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

      $action = NULL;
      $httpMethod = NULL;
      $methodName = $rMethod->name;
      if (strpos($methodName, 'get', 0) === 0) {
        $action = strtolower(substr($methodName, 3));
        $httpMethod = \Slim\Http\Request::METHOD_GET;
      }
      elseif (strpos($methodName, 'put', 0) === 0) {
        $action = strtolower(substr($methodName, 3));
        $httpMethod = \Slim\Http\Request::METHOD_PUT;
      }
      elseif (strpos($methodName, 'post', 0) === 0) {
        $action = strtolower(substr($methodName, 4));
        $httpMethod = \Slim\Http\Request::METHOD_POST;
      }
      elseif (strpos($methodName, 'delete', 0) === 0) {
        $action = strtolower(substr($methodName, 6));
        $httpMethod = \Slim\Http\Request::METHOD_DELETE;
      }

      if (!empty($action)) {
        $slim->map("/{$this->base}/{$action}",
          array($this, 'preMiddleware'),
          array($this, '_runControllerMiddlewares'),
          array($this, 'preCallable'),
          array($this, $methodName))->via($httpMethod);
        $slim->map("/{$this->base}/{$action}(/:param+)",
          array($this, 'preMiddleware'),
          array($this, '_runControllerMiddlewares'),
          array($this, 'preCallable'),
          array($this, $methodName))->via($httpMethod);
      }
    }
  }

  /**
   * Add in parameters specified using @docs-param
   *
   * @param $rMethod
   *
   * @return array
   */
  protected function getDocParameters(\ReflectionMethod $rMethod) {
    $parameters = array();
    $docsParameters = ReflectionHelper::getDocDirective($rMethod->getDocComment(), 'docs-param');
    foreach ($docsParameters as $docsParameter) {
      $paramComponents = explode(' ', $docsParameter, 5);
      $parameters[] = array(
        'Name' => $paramComponents[0],
        'Type' => $paramComponents[1],
        'Location' => $paramComponents[2],
        'Required' => $paramComponents[3] == 'required' ? 'Y' : 'N',
        'Description' => $paramComponents[4]
      );
    }

    return $parameters;
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

    $this->configureMiddlewares($this->middlewares);

    $doc = array();
    $doc['name'] = $this->documentationName;
    $doc['description'] = $parser->parse(ReflectionHelper::getDocText($rClass->getDocComment()));

    // Adding documentation for custom controller actions
    $rMethods = $rClass->getMethods(\ReflectionMethod::IS_PUBLIC);
    foreach ($rMethods as $rMethod) {
      // If there is a '@endpoint ignore' property, the function is not served as an endpoint
      if (in_array('ignore', ReflectionHelper::getDocDirective($rMethod->getDocComment(), 'endpoint'))) {
        continue;
      }

      $methodDoc = array();

      $methodDoc['InternalMethodName'] = $rMethod->name;
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

      $methodDoc['parameters'] = $this->getDocParameters($rMethod);

      // Allow controller middlewares to modify the documentation for this method
      if (!empty($this->middlewares)) {
        $middleware = reset($this->middlewares);
        $middleware->documentMethod($rClass, $rMethod, $methodDoc);
      }

      if (!empty($methodDoc)) {
        $doc['methods'][] = $methodDoc;
      }
    }

    foreach(ReflectionHelper::getDocDirective($rClass->getDocComment(), 'docs-attach-model') as $includeModelClass) {
      /**
       * @var $model \MABI\Model
       */
      $model = call_user_func($includeModelClass . '::init', $this->getApp());
      $doc['models'][] = $model->getDocOutput($parser);
    }

    return $doc;
  }
}