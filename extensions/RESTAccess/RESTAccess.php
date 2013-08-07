<?php
namespace MABI\RESTAccess;

include_once __DIR__ . '/../../Extension.php';

use MABI\Extension;

class RESTAccess extends Extension {
  public function __construct($app) {
    parent::__construct($app);
    array_push($this->middlewareDirectories, __DIR__);
  }
}
