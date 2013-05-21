<?php

namespace MABI\Middleware;

include_once __DIR__ . '/RESTAccessMiddleware.php';

/**
 * Blocks access to all standard REST functions that modify objects or a collection. This means the API can
 * only be used to read information about objects in the collection. Custom actions are allowed.
 */
class RESTReadOnlyAccess extends RESTAccessMiddleware {
  protected function doesHaveAccessToMethod($methodName) {
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
}
