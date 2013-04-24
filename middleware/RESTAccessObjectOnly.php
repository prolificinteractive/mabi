<?php

namespace MABI\Middleware;

include_once dirname(__FILE__) . '/../Utilities.php';

class RESTAccessObjectOnly extends \MABI\Middleware {

  private static function isRESTObjectOnlyOrCustom($methodName) {
    switch ($methodName) {
      case '_restGetCollection':
      case '_restPostCollection':
      case '_restPutCollection':
      case '_restDeleteCollection':
        return FALSE;
      default:
        return TRUE;
    }
  }

  /**
   * Blocks access to all standard REST functions that function on the collection. This means the API can
   * only be used on specific objects in the collection. Custom actions are allowed.
   *
   * @throws \Slim\Exception\Stop
   */
  public function call() {
    $callable = $this->getController()->getApp()->getSlim()->router()->getCurrentRoute()->getCallable();
    if (empty($callable) || !self::isRESTObjectOnlyOrCustom($callable[1])) {
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

    if (!self::isRESTObjectOnlyOrCustom($rMethod->name)) {
      $methodDoc = NULL;
      return;
    }

    if (!empty($this->next)) {
      $this->next->documentMethod($rClass, $rMethod, $methodDoc);
    }
  }

}
