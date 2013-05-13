<?php

namespace MABI\Middleware;

include_once __DIR__ . '/../Utilities.php';

class RESTAccessReadOnly extends \MABI\Middleware {

  private static function isRESTReadOnlyOrCustom($methodName) {
    switch ($methodName) {
      case '_restPostCollection':
      case '_restPutCollection':
      case '_restDeleteCollection':
      case '_restPutObject':
      case '_restDeleteObject':
        return FALSE;
      default:
        return TRUE;
    }
  }

  /**
   * Blocks access to all standard REST functions that modify objects or a collection. This means the API can
   * only be used to read information about objects in the collection. Custom actions are allowed.
   *
   * @throws \Slim\Exception\Stop
   */
  public function call() {
    $callable = $this->getController()->getApp()->getSlim()->router()->getCurrentRoute()->getCallable();
    if (empty($callable) || !self::isRESTReadOnlyOrCustom($callable[1])) {
      $this->getController()->getApp()->getSlim()->response()->status(401);
      throw new \Slim\Exception\Stop();
    }

    if (!empty($this->next)) {
      $this->next->call();
    }
  }

  /**
   * Removes the documentation for methods that the api does not have access to
   *
   * @param \ReflectionClass $rClass
   * @param \ReflectionMethod $rMethod
   * @param array $methodDoc
   */
  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
    parent::documentMethod($rClass, $rMethod, $methodDoc);

    if (!self::isRESTReadOnlyOrCustom($rMethod->name)) {
      $methodDoc = NULL;
      return;
    }

    if (!empty($this->next)) {
      $this->next->documentMethod($rClass, $rMethod, $methodDoc);
    }
  }

}
