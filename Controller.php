<?php

namespace MABI;

include_once __DIR__ . '/App.php';
include_once __DIR__ . '/Inflector.php';
include_once __DIR__ . '/Middleware.php';

class CachedRoute {
  public $path;
  public $method;
  public $httpMethod;

  function __construct($path, $method, $httpMethod) {
    $this->httpMethod = $httpMethod;
    $this->method     = $method;
    $this->path       = $path;
  }
}

class CachedControllerConstructor {
  public $base;
  public $middlewareFiles;
  public $documentationName;

  function __construct($base, $middlewareFiles, $documentationName) {
    $this->base              = $base;
    $this->documentationName = $documentationName;
    $this->middlewareFiles   = $middlewareFiles;
  }
}

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

  public function   __construct($extension) {
    $this->extension = $extension;

    $systemCache = $this->getApp()->getCacheRepository('system');
    $cacheKey = get_called_class() . get_class() . '::__construct';
    /**
     * @var $cache \MABI\CachedControllerConstructor
     */
    if($systemCache != null && is_object($cache = $systemCache->get($cacheKey))) {
      $this->base = $cache->base;

      foreach($cache->middlewareFiles as $middlewareClass => $middlewareFile) {
        $this->addMiddlewareByClass($middlewareClass, $middlewareFile);
      }

      $this->documentationName = $cache->documentationName;
      return;
    }

    if (empty($this->base)) {
      $this->base = strtolower(ReflectionHelper::stripClassName(
        ReflectionHelper::getPrefixFromControllerClass(get_called_class())));
    }

    $rClass = new \ReflectionClass(get_called_class());

    // Load middlewares from @middleware annotation
    $middlewareFiles = array();

    $annotations = $this->getApp()->getAnnotationReader()->getClassAnnotations($rClass);
    foreach ($annotations as $annotation) {
      if ($annotation instanceof \MABI\Annotations\Middleware) {
        /**
         * @var $annotation \MABI\Annotations\Middleware
         */
        $middlewareFile = ReflectionHelper::stripClassName($annotation->getClass()) . '.php';
        $this->addMiddlewareByClass($annotation->getClass(), $middlewareFile);
        $middlewareFiles[$annotation->getClass()] = $middlewareFile;
      }
    }

    if (empty($this->documentationName)) {
      $this->documentationName = ucwords(ReflectionHelper::stripClassName(ReflectionHelper::getPrefixFromControllerClass(get_called_class())));
    }

    if($systemCache != null) {
      $systemCache->forever($cacheKey, new CachedControllerConstructor($this->base, $middlewareFiles, $this->documentationName));
    }
  }

  public function addMiddlewareByClass($middlewareClass, $middlewareFile) {
    $this->extension->loadMiddleware($middlewareFile);

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

  protected function mapRoute(\Slim\Slim $slim, $path, $methodName, $httpMethod, &$cachedRoutes = NULL) {
    $slim->map($path,
      array($this, 'preMiddleware'),
      array($this, '_runControllerMiddlewares'),
      array($this, 'preCallable'),
      array($this, $methodName))->via($httpMethod);

    if (is_array($cachedRoutes)) {
      $cachedRoutes[] = new CachedRoute($path, $methodName, $httpMethod);
    }
  }

  /**
   * @param $slim \Slim\Slim
   */
  public function loadRoutes($slim) {
    $this->configureMiddlewares($this->middlewares);

    /**
     * @var $cachedRoutes CachedRoute[]
     */
    $cacheKey = get_called_class() . '.' . get_class() . '::loadRoutes';
    if (($systemCache = $this->getApp()->getCacheRepository('system')) != NULL &&
      is_array($cachedRoutes = $systemCache->get($cacheKey))
    ) {
      // Get routes from cache
      foreach ($cachedRoutes as $cachedRoute) {
        $this->mapRoute($slim, $cachedRoute->path, $cachedRoute->method, $cachedRoute->httpMethod);
      }
      return;
    }
    else {
      $cachedRoutes = array();
    }

    $rClass = new \ReflectionClass($this);
    $rMethods = $rClass->getMethods(\ReflectionMethod::IS_PUBLIC);
    $baseMethods = array();
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
        $this->mapRoute($slim, "/{$this->base}/{$action}(/?)", $methodName, $httpMethod, $cachedRoutes);
        $this->mapRoute($slim, "/{$this->base}/{$action}(/:param+)(/?)", $methodName, $httpMethod, $cachedRoutes);
      }
      elseif (!empty($httpMethod)) {
        array_push($baseMethods, array(
          'name'   => $methodName,
          'method' => $httpMethod
        ));
      }
    }

    foreach ($baseMethods as $httpMethod) {
      $this->mapRoute($slim, "/{$this->base}(/?)", $httpMethod['name'], $httpMethod['method'], $cachedRoutes);
    }

    if ($systemCache != NULL) {
      $systemCache->forever($cacheKey, $cachedRoutes);
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
      $methodDoc['URI'] = "/{$this->base}" . (empty($action) ? '' : "/{$action}");
      $methodDoc['Synopsis'] = $parser->parse(ReflectionHelper::getDocText($rMethod->getDocComment()));
      $methodDoc['parameters'] = $this->getDocParameters($rMethod);

      if (empty($methodDoc['MethodName'])) {
        $methodDoc['MethodName'] = ucwords($this->base);
      }

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
