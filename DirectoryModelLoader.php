<?php

namespace MABI;

include_once dirname(__FILE__) . '/ModelLoader.php';
include_once dirname(__FILE__) . '/Utilities.php';

/**
 * todo: docs
 */
class DirectoryModelLoader extends ModelLoader {

  protected $directory;
  protected $namespace;

  /**
   * @var string[]
   */
  protected $modelClasses;

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
    $modelClassFiles = DirectoryHelper::directoryToArray($this->directory, TRUE, '.php');

    foreach ($modelClassFiles as $modelClassFile) {
      include_once $modelClassFile;
      $this->modelClasses[] = ReflectionHelper::createClassName($this->namespace, basename($modelClassFile, '.php'));
    }

    return $this->modelClasses;
  }
}
