<?php

namespace MABI\Middleware;

include_once __DIR__ . '/../Middleware.php';
include_once __DIR__ . '/../Utilities.php';

class RESTPostOnlyAccess extends \MABI\Middleware {

  private static function isRESTPostOrCustom($methodName) {
    switch ($methodName) {
      case '_restGetCollection':
      case '_restPutCollection':
      case '_restDeleteCollection':
      case '_restGetObject':
      case '_restPutObject':
      case '_restDeleteObject':
        return FALSE;
      default:
        return TRUE;
    }
  }

  /**
   * Blocks access to all standard REST functions except for a POST to a collection. This means the API can
   * only be used to append objects to the collection and nothing else. Custom actions are allowed.
   *
   * @throws \Slim\Exception\Stop
   */
  public function call() {
    $callable = $this->getController()->getApp()->getSlim()->router()->getCurrentRoute()->getCallable();
    if (empty($callable) || !self::isRESTPostOrCustom($callable[1])) {
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

    if (!self::isRESTPostOrCustom($rMethod->name)) {
      $methodDoc = NULL;
      return;
    }

    if (!empty($this->next)) {
      $this->next->documentMethod($rClass, $rMethod, $methodDoc);
    }
  }

}
