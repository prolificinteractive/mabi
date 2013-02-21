<?php

namespace MABI;

include_once dirname(__FILE__) . '/ModelLoader.php';

/**
 * todo: docs
 */
class DirectoryModelLoader extends ModelLoader {

  protected $directory;
  protected $namespace;

  private function directoryToArray($directory, $recursive) {
    $array_items = array();
    if ($handle = opendir($directory)) {
      while (FALSE !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
          if (is_dir($directory . "/" . $file)) {
            if ($recursive) {
              $array_items = array_merge($array_items, directoryToArray($directory . "/" . $file, $recursive));
            }
            $file = $directory . "/" . $file;
            $array_items[] = preg_replace("/\/\//si", "/", $file);
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

  /**
   * todo: docs
   *
   * @param $directory string
   * @param $namespace string|null
   */
  public function __construct($directory, $namespace = NULL) {
    $this->directory = $directory;
    $this->namespace = $namespace;
  }

  public function loadModels() {
    $modelClassFiles = $this->directoryToArray($this->directory, TRUE);

    foreach ($modelClassFiles as $modelClassFile) {
      include_once $modelClassFile;
    }
  }
}
