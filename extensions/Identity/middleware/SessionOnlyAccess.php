<?php

namespace MABI\Identity\Middleware;

use MABI\Middleware;

include_once __DIR__ . '/../../../Middleware.php';

class SessionOnlyAccess extends Middleware {

  /**
   * Call
   *
   * Makes sure that a session exists in order for the user to have access
   *
   * Perform actions specific to this middleware and optionally
   * call the next downstream middleware.
   */
  public function call() {
    // A session is required to access this call
    if (empty($this->getApp()->getRequest()->session)) {
      $this->getApp()->returnError('Not properly authenticated for this route', 401, 1007);
    }

    if (!empty($this->next)) {
      $this->next->call();
    }
  }

  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
    parent::documentMethod($rClass, $rMethod, $methodDoc);

    foreach ($methodDoc['parameters'] as $k => $parameter) {
      if ($parameter['Name'] == 'SESSION') {
        $methodDoc['parameters'][$k]['Required'] = 'Y';
      }
    }

    if (!empty($this->next)) {
      $this->next->documentMethod($rClass, $rMethod, $methodDoc);
    }
  }
}
