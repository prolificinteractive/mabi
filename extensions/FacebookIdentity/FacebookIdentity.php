<?php
namespace MABI\FacebookIdentity;

include_once __DIR__ . '/../../Extension.php';
include_once __DIR__ . '/../../DirectoryModelLoader.php';
include_once __DIR__ . '/../../DirectoryControllerLoader.php';

use MABI\DirectoryControllerLoader;
use MABI\DirectoryModelLoader;
use MABI\Extension;

class FacebookIdentity extends Extension {

  /**
   * @var bool
   */
  protected $facebookOnly;

  /**
   * @return boolean
   */
  public function getFacebookOnly() {
    return $this->facebookOnly;
  }

  public function __construct(\MABI\App $app, \MABI\Identity\Identity $identityExtension, $facebookOnly = FALSE) {
    parent::__construct($app);

    $this->facebookOnly = $facebookOnly;

    array_push($this->middlewareDirectories, __DIR__ . '/middleware');

    $this->addExtension($identityExtension);
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
