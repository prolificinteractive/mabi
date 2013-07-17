<?php

namespace MABI;

abstract class ControllerLoader {
  /**
   * @return Controller[]
   */
  abstract function getControllers();
}