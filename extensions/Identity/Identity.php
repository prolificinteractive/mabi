<?php
namespace MABI\Identity;

include_once __DIR__ . '/../../App.php';
include_once __DIR__ . '/../RESTAccess/RESTAccess.php';
include_once __DIR__ . '/../../Extension.php';
include_once __DIR__ . '/../../DirectoryModelLoader.php';
include_once __DIR__ . '/../../DirectoryControllerLoader.php';
include_once __DIR__ . '/Errors.php';

use MABI\App;
use MABI\DirectoryControllerLoader;
use MABI\DirectoryModelLoader;
use MABI\Extension;
use MABI\RESTAccess\RESTAccess;

class Identity extends Extension {
  public function __construct(App $app, RESTAccess $restAccessExtension) {
    parent::__construct($app);
    array_push($this->middlewareDirectories, __DIR__ . '/middleware');

    $this->addExtension($restAccessExtension);
    $this->setModelLoaders(array(
      new DirectoryModelLoader(__DIR__ . '/models', 'MABI\Identity')
    ));
    $this->setControllerLoaders(array(
      new DirectoryControllerLoader(__DIR__ . '/controllers', $this, 'MABI\Identity')
    ));
    $this->getExtensionModelClasses();

    $this->getApp()->getErrorResponseDictionary()->overrideErrorResponses(new Errors());
  }

  public static function passHash($password, $salt) {
    return hash_hmac('sha256', $password, $salt);
  }

}
