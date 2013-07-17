<?php

namespace MABI;

abstract class Middleware {
  /**
   * @var \MABI\Controller
   */
  protected $controller;

  /**
   * @var \MABI\Middleware Reference to the next downstream middleware
   */
  protected $next;

  /**
   * todo: docs
   *
   * @param  \MABI\Controller $controller
   */
  public function setController($controller) {
    $this->controller = $controller;
  }

  /**
   * todo: docs
   *
   * @return \MABI\Controller
   */
  public function getController() {
    return $this->controller;
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

  /**
   * Call
   *
   * Perform actions specific to this middleware and optionally
   * call the next downstream middleware.
   */
  abstract public function call();
}
