<?php

namespace MABI;

include_once __DIR__ . '/ModelLoader.php';
include_once __DIR__ . '/Utilities.php';

/**
 * todo: docs
 */
class DirectoryModelLoader extends ModelLoader {

  protected $directory;
  protected $namespace;

  /**
   * @var string[]
   */
  protected $modelClasses = array();

  /**
   * todo: docs
   *
   * @param $directory string
   * @param $namespace string|null
   */
  public function __construct($directory, $namespace = NULL) {
    $this->directory = $directory;
    $this->namespace = $namespace;

    // Make sure all PHP files in the directory are included
    $modelClassFiles = DirectoryHelper::directoryToArray($this->directory, TRUE, '.php');
    foreach ($modelClassFiles as $modelClassFile) {
      include_once $modelClassFile;
    }
  }

  public function loadModels() {
    $modelClassFiles = DirectoryHelper::directoryToArray($this->directory, TRUE, '.php');

    foreach ($modelClassFiles as $modelClassFile) {
      $this->modelClasses[] = ReflectionHelper::createClassName($this->namespace, basename($modelClassFile, '.php'));
    }

    return $this->modelClasses;
  }
}
