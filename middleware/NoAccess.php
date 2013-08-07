<?php

namespace MABI\Middleware;

use MABI\Middleware;

include_once __DIR__ . '/../Middleware.php';

class NoAccess extends Middleware {
  public function call() {
    $this->getApp()->returnError('Not properly authenticated for this route', 401, 1007);
  }

  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
    parent::documentMethod($rClass, $rMethod, $methodDoc);

    $methodDoc = NULL;
    return;
  }
}
