<?php

namespace MABI\RESTAccess;

include_once __DIR__ . '/RESTAccessMiddleware.php';

/**
 * Blocks access to all standard REST functions that modify objects or a collection. This means the API can
 * only be used to read information about objects in the collection. Custom actions are allowed.
 */
class ReadOnly extends RESTAccessMiddleware {
  protected function doesHaveAccessToMethod($methodName) {
    switch ($methodName) {
      case 'post':
      case 'put':
      case 'delete':
      case '_restPutResource':
      case '_restDeleteResource':
        return FALSE;
      default:
        return TRUE;
    }
  }
}
