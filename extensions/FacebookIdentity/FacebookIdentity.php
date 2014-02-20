<?php
namespace MABI\FacebookIdentity;

include_once __DIR__ . '/../../Extension.php';
include_once __DIR__ . '/../../DirectoryModelLoader.php';
include_once __DIR__ . '/../../DirectoryControllerLoader.php';
include_once __DIR__ . '/../Identity/Identity.php';
include_once __DIR__ . '/Errors.php';

use MABI\DirectoryControllerLoader;
use MABI\DirectoryModelLoader;
use MABI\Extension;

class FacebookIdentity extends Extension {

  public function __construct(\MABI\App $app, \MABI\Identity\Identity $identityExtension, $facebookOnly = FALSE) {
    parent::__construct($app);

    $this->facebookOnly = $facebookOnly;

    array_push($this->middlewareDirectories, __DIR__ . '/middleware');

    $this->addExtension($identityExtension);
    $this->setModelLoaders(array(
      new DirectoryModelLoader(__DIR__ . '/models', $this, 'MABI\FacebookIdentity')
    ));
    $this->setControllerLoaders(array(
      new DirectoryControllerLoader(__DIR__ . '/controllers', $this, 'MABI\FacebookIdentity')
    ));
    $this->getExtensionModelClasses();

    foreach($this->getControllers() as $controller) {
      $controller->setFacebookOnly($facebookOnly);
    }

    $this->getApp()->getErrorResponseDictionary()->overrideErrorResponses(new Errors());
  }
}
