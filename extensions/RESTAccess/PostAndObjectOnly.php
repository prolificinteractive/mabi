<?php

namespace MABI\RESTAccess;

include_once __DIR__ . '/RESTAccessMiddleware.php';

/**
 * Blocks access to collection REST functions except for a POST. Custom actions are allowed.
 */
class PostAndObjectOnly extends RESTAccessMiddleware {
  protected function doesHaveAccessToMethod($methodName) {
    switch ($methodName) {
      case '_restGetCollection':
      case '_restPutCollection':
      case '_restDeleteCollection':
        return FALSE;
      default:
        return TRUE;
    }
  }
}
