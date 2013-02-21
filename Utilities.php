<?php

namespace MABI;

class ReflectionHelper {
  public static function stripClassName($fullName) {
    $components = explode('\\', $fullName);
    return $components[count($components) - 1];
  }
}