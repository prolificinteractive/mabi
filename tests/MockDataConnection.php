<?php

namespace MABI\Testing;

use MABI\DataConnection;

abstract class MockDataConnection implements DataConnection {

  public function getDefaultIdColumn() {
    return 'id';
  }

  public function convertToNativeId($stringId) {
    return intval($stringId);
  }

  public function convertFromNativeId($nativeId) {
    return strval($nativeId);
  }

}