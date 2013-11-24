<?php

namespace MABI\RESTAccess;

include_once __DIR__ . '/RESTAccessMiddleware.php';

/**
 * Blocks access to all standard REST functions that function on the collection. This means the API can
 * only be used on specific objects in the collection. Custom actions are allowed.
 */
class ObjectOnly extends RESTAccessMiddleware {
  protected function doesHaveAccessToMethod($methodName) {
    switch ($methodName) {
      case 'get':
      case 'post':
      case 'put':
      case 'delete':
        return FALSE;
      default:
        return TRUE;
    }
  }
}
