<?php

namespace MABI\Middleware;

class APIApplicationOnlyAccess extends \MABI\Middleware {
  /**
   * Call
   *
   * Makes sure that the application has been identified
   *
   * Perform actions specific to this middleware and optionally
   * call the next downstream middleware.
   */
  public function call() {
    if(empty($this->getController()->getApp()->getSlim()->request()->apiApplication)) {
      $this->getController()->getApp()->getSlim()->response()->status(401);
      throw new \Slim\Exception\Stop();
    }

     if (!empty($this->next)) {
      $this->next->call();
    }
  }
}
