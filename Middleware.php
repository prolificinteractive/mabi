<?php

namespace MABI;

include_once __DIR__ . '/Controller.php';

abstract class Middleware {
  /**
   * @var \MABI\App
   */
  protected $app;

  /**
   * @var \MABI\Middleware Reference to the next downstream middleware
   */
  protected $next;

  /**
   * @param \MABI\App $app
   */
  public function setApp($app) {
    $this->app = $app;
  }

  public function getRouteCallable() {
    return $this->app->getSlim()->router()->getCurrentRoute()->getCallable();
  }

  public function getApp() {
    return $this->app;
  }

  /**
   * Set next middleware
   *
   * This method injects the next downstream middleware into
   * this middleware so that it may optionally be called
   * when appropriate.
   *
   * @param \MABI\Middleware $nextMiddleware
   */
  public function setNextMiddleware($nextMiddleware) {
    $this->next = $nextMiddleware;
  }

  /**
   * Get next middleware
   *
   * This method retrieves the next downstream middleware
   * previously injected into this middleware.
   *
   * @return \MABI\Middleware
   */
  public function getNextMiddleware() {
    return $this->next;
  }

  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
  }

  /**
   * Call
   *
   * Perform actions specific to this middleware and optionally
   * call the next downstream middleware.
   */
  abstract public function call();
}
