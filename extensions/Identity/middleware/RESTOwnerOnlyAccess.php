<?php

namespace MABI\Identity\Middleware;

use MABI\Middleware;
use MABI\Identity\Session;
use MABI\ReflectionHelper;
use Slim\Exception\Stop;

include_once __DIR__ . '/../../../Middleware.php';

class RESTOwnerOnlyAccess extends Middleware {

  protected function isCollectionCallable($methodName) {
    switch ($methodName) {
      case '_restGetCollection':
      case '_restPostCollection':
      case '_restPutCollection':
      case '_restDeleteCollection':
        return TRUE;
      default:
        if (strpos($methodName, 'get', 0) === 0 ||
          strpos($methodName, 'put', 0) === 0 ||
          strpos($methodName, 'post', 0) === 0 ||
          strpos($methodName, 'delete', 0) === 0
        ) {
          return TRUE;
        }
        return FALSE;
    }
  }

  /**
   * Call
   *
   * Pulls out a anonymous sent from an http header
   *
   * Perform actions specific to this middleware and optionally
   * call the next downstream middleware.
   */
  public function call() {
    // Owner access does not apply for Collection level functions
    $callable = $this->getController()->getApp()->getSlim()->router()->getCurrentRoute()->getCallable();
    if (empty($callable) || $this->isCollectionCallable($callable[1])) {
      if (!empty($this->next)) {
        $this->next->call();
      }
      return;
    }

    // A session is required to access these objects
    if (!isset($this->getController()->getApp()->getSlim()->request()->session)) {
      $this->getController()->getApp()->getSlim()->response()->status(401);
      throw new Stop();
    }

    /**
     * @var $restController \MABI\RESTModelController
     * @var $session \MABI\Identity\Session
     */
    $session = $this->getController()->getApp()->getSlim()->request()->session;
    $restController = $this->getController();

    $rClass = new \ReflectionClass($restController->getModelClass());
    $ownerProperty = 'owner';
    foreach ($rClass->getProperties() as $rProperty) {
      if (in_array('owner', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field'))) {
        $ownerProperty = $rProperty->getName();
        break;
      }
    }

    $model = $restController->getModel();
    if (empty($session) || empty($model) || empty($session->user) || empty($model->{$ownerProperty}) ||
      $session->user != $restController->getModel()->{$ownerProperty}
    ) {
      // Don't give access to endpoint if the sessions
      $this->getController()->getApp()->getSlim()->response()->status(401);
      throw new Stop();
    }

    if (!empty($this->next)) {
      $this->next->call();
    }
  }

  protected function callNextDocumenter($rClass, $rMethod, $methodDoc) {
    if (!empty($this->next)) {
      $this->next->documentMethod($rClass, $rMethod, $methodDoc);
    }
  }

  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
    parent::documentMethod($rClass, $rMethod, $methodDoc);

    // Owner access does not apply for Collection level functions
    $route = $this->getController()->getApp()->getSlim()->router()->getCurrentRoute();
    if (empty($route)) {
      $this->callNextDocumenter($rClass, $rMethod, $methodDoc);
      return;
    }
    $callable = $route->getCallable();
    if (empty($callable) || !$this->isCollectionCallable($callable[1])) {
      $this->callNextDocumenter($rClass, $rMethod, $methodDoc);
      return;
    }

    foreach ($methodDoc['parameters'] as $k => $parameter) {
      if ($parameter['Name'] == 'SESSION') {
        $methodDoc['parameters'][$k]['Required'] = 'Y';
      }
    }

    $this->callNextDocumenter($rClass, $rMethod, $methodDoc);
  }
}
