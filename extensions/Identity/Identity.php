<?php
namespace MABI\Identity;

include_once __DIR__ . '/../../Extension.php';
include_once __DIR__ . '/../../DirectoryModelLoader.php';
include_once __DIR__ . '/../../DirectoryControllerLoader.php';

use MABI\DirectoryControllerLoader;
use MABI\DirectoryModelLoader;
use MABI\Extension;

class Identity extends Extension {
  public function __construct($app, $restAccessExtension) {
    parent::__construct($app);
    array_push($this->middlewareDirectories, __DIR__ . '/middleware');

    $this->addExtension($restAccessExtension);
    $this->setModelLoaders(array(
      new DirectoryModelLoader(__DIR__ . '/models', 'MABI\Identity')
    ));
    $this->setControllerLoaders(array(
      new DirectoryControllerLoader(__DIR__ . '/controllers', $this, 'MABI\Identity')
    ));
  }

  public static function passHash($password, $salt) {
    return hash_hmac('sha256', $password, $salt);
  }
}
