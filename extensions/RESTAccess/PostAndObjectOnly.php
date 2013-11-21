<?php

namespace MABI\RESTAccess;

include_once __DIR__ . '/RESTAccessMiddleware.php';

/**
 * Blocks access to collection REST functions except for a POST. Custom actions are allowed.
 */
class PostAndObjectOnly extends RESTAccessMiddleware {
  protected function doesHaveAccessToMethod($methodName) {
    switch ($methodName) {
      case 'get':
      case 'put':
      case 'delete':
        return FALSE;
      default:
        return TRUE;
    }
  }
}
