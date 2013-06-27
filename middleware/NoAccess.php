<?php

namespace MABI\Middleware;

use MABI\Middleware;
use Slim\Exception\Stop;

include_once __DIR__ . '/../Middleware.php';

class NoAccess extends Middleware {
  public function call() {
    $this->getController()->getApp()->getSlim()->response()->status(401);
    throw new Stop('You do not have access to this area');
  }

  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
    parent::documentMethod($rClass, $rMethod, $methodDoc);

    $methodDoc = NULL;
    return;
  }
}
