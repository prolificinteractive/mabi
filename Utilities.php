<?php

namespace MABI;

class ReflectionHelper {
  public static function stripClassName($fullName) {
    $components = explode('\\', $fullName);
    return $components[count($components) - 1];
  }

  public static function getPrefixFromControllerClass($controllerClass) {
    if (substr($controllerClass, -strlen('Controller')) === 'Controller') {
      return substr($controllerClass, 0, strlen($controllerClass) - strlen('Controller'));
    }

    throw new \Exception('Cannot find model for model controller ' . $controllerClass);
  }

  public static function createClassName($namespace, $className) {
    return (empty($namespace) ? '' : $namespace) . "\\{$className}";
  }
}

class DirectoryHelper {
  public static function directoryToArray($directory, $recursive = FALSE, $extension = NULL) {
    $array_items = array();
    if ($handle = opendir($directory)) {
      while (FALSE !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && (empty($extension) ||
          (!empty($extension) && substr($file, -strlen($extension)) === $extension))
        ) {

          if (is_dir($directory . "/" . $file)) {
            if ($recursive) {
              $array_items = array_merge($array_items, self::directoryToArray($directory . "/" . $file, $recursive));
            }
          }
          else {
            $file = $directory . "/" . $file;
            $array_items[] = preg_replace("/\/\//si", "/", $file);
          }
        }
      }
      closedir($handle);
    }
    return $array_items;
  }
}