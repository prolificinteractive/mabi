<?php

namespace MABI\RESTAccess;

include_once __DIR__ . '/RESTAccessMiddleware.php';

/**
 * Blocks access to all standard REST functions except for a POST to a collection. This means the API can
 * only be used to append objects to the collection and nothing else. Custom actions are allowed.
 */
class PostOnly extends RESTAccessMiddleware {
  protected function doesHaveAccessToMethod($methodName) {
    switch ($methodName) {
      case '_restGetCollection':
      case '_restPutCollection':
      case '_restDeleteCollection':
      case '_restGetResource':
      case '_restPutResource':
      case '_restDeleteResource':
        return FALSE;
      default:
        return TRUE;
    }
  }
}
