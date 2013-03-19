<?php

namespace MABI\Middleware;

class AnonymousIdentifier extends \Slim\Middleware {
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
    $this->anonymousId = $this->app->request()->headers('anonuuid');
if(!empty($this->next))
    $this->next->call();
  }
}
