<?php

namespace MABI\Middleware;

class RESTAccessPostOnly extends \Slim\Middleware {
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
    $methods = $this->getApplication()->router()->getCurrentRoute()->getHttpMethods();
    if(empty($methods) || $methods[0] != 'POST') {
      $this->getApplication()->response()->status(401);
      throw new \Slim\Exception\Stop();
    }

/*    if($this->getApplication()->router()->getCurrentRoute()->getHttpMethods())
    var_dump($this->getApplication()->router()->getCurrentRoute()->getHttpMethods());
    die();
*/
//    $this->anonymousId = $this->app->request()->headers('anonuuid');

    if(!empty($this->next)) $this->next->call();
  }
}
