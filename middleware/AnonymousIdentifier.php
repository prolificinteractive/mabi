<?php

namespace MABI\Middleware;

class AnonymousIdentifier extends \MABI\Middleware {
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
    $this->anonymousId = $this->getController()->getApp()->getSlim()->request()->headers('anonuuid');
    $this->getController()->getApp()->getSlim()->request()->anonymousId = $this->anonymousId;
    if (!empty($this->next)) {
      $this->next->call();
    }
  }
}
