<?php

namespace MABI\Middleware;

use MABI\Middleware;
use MABI\DefaultAppErrors;

include_once __DIR__ . '/../Middleware.php';

class NoAccess extends Middleware {
  public function call() {
    $this->getApp()->returnError(DefaultAppErrors::$NOT_AUTHORIZED);
  }

  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
    parent::documentMethod($rClass, $rMethod, $methodDoc);

    $methodDoc = NULL;
    return;
  }
}
