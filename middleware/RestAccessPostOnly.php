<?php

namespace MABI\Middleware;

class RESTAccessPostOnly extends \MABI\Middleware {
  public $anonymousId = NULL;

  /**
   * Call
   *
   * Pulls out a anonymous sent from an http header
   *
   * Perform actions specific to this middleware and optionally
   * call the next downstream middleware.
   */
  public function call() {
    $methods = $this->getController()->getApp()->getSlim()->router()->getCurrentRoute()->getHttpMethods();
    if(empty($methods) || $methods[0] != 'POST') {
      $this->getController()->getApp()->getSlim()->response()->status(401);
      throw new \Slim\Exception\Stop();
    }

    if(!empty($this->next)) $this->next->call();
  }
}
