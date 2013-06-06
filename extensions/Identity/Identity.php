<?php
namespace MABI\Identity;

use MABI\DirectoryControllerLoader;
use MABI\DirectoryModelLoader;
use MABI\Extension;

class Identity extends Extension {
  public function __construct() {
    array_push($this->middlewareDirectories, __DIR__ . '/middleware');
    $this->setModelLoaders(array(
      new DirectoryModelLoader(__DIR__ . '/models', 'MABI\Identity')
    ));
    $this->setControllerLoaders(array(
      new DirectoryControllerLoader(__DIR__ . '/controllers', 'MABI\Identity')
    ));
  }

  public static function passHash($password, $salt) {
    return hash_hmac('sha256', $password, $salt);
  }
}
