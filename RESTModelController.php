<?php

namespace MABI;

include_once __DIR__ . '/Inflector.php';
include_once __DIR__ . '/Utilities.php';
include_once __DIR__ . '/ModelController.php';

/**
 * todo: docs
 */
class RESTModelController extends ModelController {
  /**
   * @var \Mabi\Model
   */
  protected $model = NULL;

  /**
   * @return \Mabi\Model
   */
  public function getModel() {
    return $this->model;
  }

  public function _restGetCollection() {
    /**
     * @var $model Model
     */
    $model = call_user_func($this->modelClass . '::init', $this->getApp());
    echo json_encode($model->findAll());
  }

  public function _restPostCollection() {
    $this->model = call_user_func($this->modelClass . '::init', $this->getApp());
    $this->model->loadParameters($this->getApp()->getSlim()->request()->post());
    $this->model->insert();
    echo $this->model->outputJSON();
  }

  public function _restPutCollection() {
    // todo: implement
  }

  public function _restDeleteCollection() {
    // todo: implement
  }

  public function _restGetObject($id) {
    /**
     * @var $model Model
     */
    echo $this->model->outputJSON();
  }

  public function _restPutObject($id) {
    $this->model->loadParameters($this->getApp()->getSlim()->request()->post(), $id);
    $this->model->save();
  }

  public function _restDeleteObject($id) {
    $this->model->delete();
  }

  /**
   * @param $route \Slim\Route
   */
  public function _readModel($route) {
    $this->model = call_user_func($this->modelClass . '::init', $this->getApp());
    $this->model->findById($route->getParam('id'));
  }

  /**
   * @param $slim \Slim\Slim
   */
  public function loadRoutes($slim) {
    parent::loadRoutes($slim);

    /**
     * Automatically generates routes for the following
     *
     * GET      /<model>          get all models by id
     * POST     /<model>          creates a new model
     * PUT      /<model>          bulk creates full new model collection
     * DELETE   /<model>          deletes all models
     * GET      /<model>/<id>     gets one model's full details
     * PUT      /<model>/<id>     updates the model
     * DELETE   /<model>/<id>     deletes the model
     */

    // todo: add API versioning
    $slim->get("/{$this->base}",
      array($this, 'preMiddleware'),
      array($this, '_runControllerMiddlewares'),
      array($this, 'preCallable'),
      array($this, '_restGetCollection'));
    $slim->post("/{$this->base}",
      array($this, 'preMiddleware'),
      array($this, '_runControllerMiddlewares'),
      array($this, 'preCallable'),
      array($this, '_restPostCollection'));
    $slim->put("/{$this->base}",
      array($this, 'preMiddleware'),
      array($this, '_runControllerMiddlewares'),
      array($this, 'preCallable'),
      array($this, '_restPutCollection'));
    $slim->delete("/{$this->base}",
      array($this, 'preMiddleware'),
      array($this, '_runControllerMiddlewares'),
      array($this, 'preCallable'),
      array($this, '_restDeleteCollection'));
    $slim->get("/{$this->base}/:id",
      array($this, 'preMiddleware'),
      array($this, '_readModel'),
      array($this, '_runControllerMiddlewares'),
      array($this, 'preCallable'),
      array($this, '_restGetObject'));
    $slim->put("/{$this->base}/:id",
      array($this, 'preMiddleware'),
      array($this, '_readModel'),
      array($this, '_runControllerMiddlewares'),
      array($this, 'preCallable'),
      array($this, '_restPutObject'));
    $slim->delete("/{$this->base}/:id",
      array($this, 'preMiddleware'),
      array($this, '_readModel'),
      array($this, '_runControllerMiddlewares'),
      array($this, 'preCallable'),
      array($this, '_restDeleteObject'));

    /**
     * Gets other automatically generated routes following the pattern:
     * /BASE/:id/ACTION(/:param+) from methods named rest<METHOD><ACTION>()
     * where <METHOD> is GET, PUT, POST, or DELETE
     */
    $rClass = new \ReflectionClass($this);
    $rMethods = $rClass->getMethods(\ReflectionMethod::IS_PUBLIC);
    foreach ($rMethods as $rMethod) {
      $action = NULL;
      $httpMethod = NULL;
      $methodName = $rMethod->name;
      if (strpos($methodName, 'restGet', 0) === 0) {
        $action = strtolower(substr($methodName, 7));
        $httpMethod = \Slim\Http\Request::METHOD_GET;
      }
      elseif (strpos($methodName, 'restPut', 0) === 0) {
        $action = strtolower(substr($methodName, 7));
        $httpMethod = \Slim\Http\Request::METHOD_PUT;
      }
      elseif (strpos($methodName, 'restPost', 0) === 0) {
        $action = strtolower(substr($methodName, 8));
        $httpMethod = \Slim\Http\Request::METHOD_POST;
      }
      elseif (strpos($methodName, 'restDelete', 0) === 0) {
        $action = strtolower(substr($methodName, 10));
        $httpMethod = \Slim\Http\Request::METHOD_DELETE;
      }

      if (!empty($action)) {
        $slim->map("/{$this->base}/:id/{$action}",
          array($this, 'preMiddleware'),
          array($this, '_readModel'),
          array($this, '_runControllerMiddlewares'),
          array($this, 'preCallable'),
          array($this, $methodName))->via($httpMethod);
        $slim->map("/{$this->base}/:id/{$action}(/:param)",
          array($this, 'preMiddleware'),
          array($this, '_readModel'),
          array($this, '_runControllerMiddlewares'),
          array($this, 'preCallable'),
          array($this, $methodName))->via($httpMethod);
      }
    }
  }

  private function getRestMethodDocJSON(Parser $parser, $methodName, $httpMethod, $url, $rClass,
                                        $method, $includesId = FALSE) {
    $methodDoc = array();

    $methodDoc['MethodName'] = $methodName;
    $methodDoc['HTTPMethod'] = $httpMethod;
    $methodDoc['URI'] = $url;
    $rMethod = new \ReflectionMethod($this, $method);
    $methodDoc['Synopsis'] = $parser->parse(ReflectionHelper::getDocText($rMethod->getDocComment()));
    if ($includesId) {
      $methodDoc['parameters'][] = array(
        'Name' => 'id',
        'Required' => 'Y',
        'Type' => 'string',
        'Location' => 'url',
        'Description' => 'The id of the resource'
      );
    }
    else {
      $methodDoc['parameters'] = array();
    }

    // Allow controller middlewares to modify the documentation for this method
    if (!empty($this->middlewares)) {
      $middleware = reset($this->middlewares);
      $middleware->documentMethod($rClass, $rMethod, $methodDoc);
    }

    return $methodDoc;
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
    $doc = parent::getDocJSON($parser);

    $rClass = new \ReflectionClass(get_called_class());

    $methodDoc = $this->getRestMethodDocJSON($parser, 'Get Full Collection',
      'GET', "/{$this->base}", $rClass, '_restGetCollection');
    if (!empty($methodDoc)) {
      $doc['methods'][] = $methodDoc;
    }
    $methodDoc = $this->getRestMethodDocJSON($parser, 'Add to Collection',
      'POST', "/{$this->base}", $rClass, '_restPostCollection');
    if (!empty($methodDoc)) {
      $doc['methods'][] = $methodDoc;
    }
    $methodDoc = $this->getRestMethodDocJSON($parser, 'Replace Full Collection',
      'PUT', "/{$this->base}", $rClass, '_restPutCollection');
    if (!empty($methodDoc)) {
      $doc['methods'][] = $methodDoc;
    }
    $methodDoc = $this->getRestMethodDocJSON($parser, 'Delete Full Collection',
      'DELETE', "/{$this->base}", $rClass, '_restDeleteCollection');
    if (!empty($methodDoc)) {
      $doc['methods'][] = $methodDoc;
    }
    $methodDoc = $this->getRestMethodDocJSON($parser, 'Get Resource',
      'GET', "/{$this->base}/:id", $rClass, '_restGetObject', TRUE);
    if (!empty($methodDoc)) {
      $doc['methods'][] = $methodDoc;
    }
    $methodDoc = $this->getRestMethodDocJSON($parser, 'Update Resource',
      'PUT', "/{$this->base}/:id", $rClass, '_restPutObject', TRUE);
    if (!empty($methodDoc)) {
      $doc['methods'][] = $methodDoc;
    }
    $methodDoc = $this->getRestMethodDocJSON($parser, 'Delete Resource',
      'DELETE', "/{$this->base}/:id", $rClass, '_restDeleteObject', TRUE);
    if (!empty($methodDoc)) {
      $doc['methods'][] = $methodDoc;
    }

    // Add documentation for custom rest actions
    $rMethods = $rClass->getMethods(\ReflectionMethod::IS_PUBLIC);
    foreach ($rMethods as $rMethod) {
      $methodDoc = array();

      if (strpos($rMethod->name, 'restGet', 0) === 0) {
        $methodDoc['MethodName'] = substr($rMethod->name, 7);
        $methodDoc['HTTPMethod'] = 'GET';
      }
      elseif (strpos($rMethod->name, 'restPut', 0) === 0) {
        $methodDoc['MethodName'] = substr($rMethod->name, 7);
        $methodDoc['HTTPMethod'] = 'PUT';
      }
      elseif (strpos($rMethod->name, 'restPost', 0) === 0) {
        $methodDoc['MethodName'] = substr($rMethod->name, 8);
        $methodDoc['HTTPMethod'] = 'POST';
      }
      elseif (strpos($rMethod->name, 'restDelete', 0) === 0) {
        $methodDoc['MethodName'] = substr($rMethod->name, 10);
        $methodDoc['HTTPMethod'] = 'DELETE';
      }
      else {
        continue;
      }
      $action = strtolower($methodDoc['MethodName']);
      $methodDoc['URI'] = "/{$this->base}/:id/{$action}";
      $methodDoc['Synopsis'] = $parser->parse(ReflectionHelper::getDocText($rMethod->getDocComment()));
      $methodDoc['parameters'][] = array(
        'Name' => 'id',
        'Required' => 'Y',
        'Type' => 'string',
        'Location' => 'url',
        'Description' => 'The id of the resource'
      );

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