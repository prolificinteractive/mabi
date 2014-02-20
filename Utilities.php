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

  public static function getDocDirective($docComments, $property) {
    $matches = array();
    preg_match_all('/\@' . $property . '\s(.*)\s/', $docComments, $matches);
    return $matches[1];
  }

  public static function getDocText($docComments) {
    // Doc comments include things like '   /** ' and '    * ' before the actual text. This function
    // cleans all of that up.

    $docComments = preg_replace('/^\s*\/\*\*.*\\n/m', '', $docComments); // Gets rid of '  /** '
    $docComments = preg_replace('/^\s*\*\/\s*/m', '', $docComments); // gets rid of '   */'
    $docComments = preg_replace('/^\s*\*\s*\n/m', "\n", $docComments); // gets rid of blank '  * '
    $docComments = preg_replace('/^\s*\*\s/m', '', $docComments); // gets rid of the pre '   * '
    // Gets rid of doc directives
    $docComments = preg_replace('/^\@.*\\n/m', '', $docComments);
    $docComments = preg_replace('/\@docs-jsondesc-start(.|\n|\r)*\@docs-jsondesc-end\n/m', '', $docComments);
    return $docComments;
  }

  public static function createClassName($namespace, $className) {
    return (empty($namespace) ? '' : $namespace) . "\\{$className}";
  }
}

class DirectoryHelper {
  /**
   * todo: docs
   *
   * @param string $directory
   * @param bool   $recursive
   * @param string $extension
   *
   * @return array
   */
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