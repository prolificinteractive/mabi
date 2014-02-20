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
   * @var \MABI\Extension
   */
  protected $extension;

  /**
   * @var string[]
   */
  protected $modelClasses = array();

  protected $modelClassFiles = array();

  /**
   * todo: docs
   *
   * @param $directory string
   * @param $extension \MABI\Extension
   * @param $namespace string|null
   */
  public function __construct($directory, $extension, $namespace = NULL) {
    $this->directory = $directory;
    $this->namespace = $namespace;
    $this->extension = $extension;

    if (($systemCache = $this->extension->getApp()->getCacheRepository('system')) != NULL &&
      is_array($modelClassFiles = $systemCache->get($this->directory . '::fileList'))
    ) {
      $this->modelClassFiles = $modelClassFiles;
    }
    else {
      // Make sure all PHP files in the directory are included
      $this->modelClassFiles = DirectoryHelper::directoryToArray($this->directory, TRUE, '.php');
      if ($systemCache != NULL) {
        $systemCache->forever($this->directory . '::fileList', $this->modelClassFiles);
      }
    }

    foreach ($this->modelClassFiles as $modelClassFile) {
      include_once $modelClassFile;
    }
  }

  public function loadModels() {
    foreach ($this->modelClassFiles as $modelClassFile) {
      $this->modelClasses[] = ReflectionHelper::createClassName($this->namespace, basename($modelClassFile, '.php'));
    }

    return $this->modelClasses;
  }
}
