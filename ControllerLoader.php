<?php

namespace MABI;

class ControllerLoader {
  /**
   * @var \MABI\Controller[]
   */
  protected $controllers = array();

  /**
   * @param \MABI\Controller[] $controllers
   */
  public function setControllers(array $controllers) {
    $this->controllers = $controllers;
  }

  /**
   * @return Controller[]
   */
  public function getControllers() {
    return $this->controllers;
  }
}