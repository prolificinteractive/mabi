<?php

namespace MABI\Middleware;

include_once __DIR__ . '/../Middleware.php';

class APIApplicationOnlyAccess extends \MABI\Middleware {
  /**
   * Call
   *
   * Makes sure that the application has been identified
   *
   * Perform actions specific to this middleware and optionally
   * call the next downstream middleware.
   */
  public function call() {
    if (empty($this->getController()->getApp()->getSlim()->request()->apiApplication)) {
      $this->getController()->getApp()->returnError('Not properly authenticated for this route', 401, 1007);
    }

    if (!empty($this->next)) {
      $this->next->call();
    }
  }

  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
    parent::documentMethod($rClass, $rMethod, $methodDoc);

    // todo: adjust if not only shared-secret access
    foreach ($methodDoc['parameters'] as $k => $parameter) {
      if ($parameter['Name'] == 'shared-secret') {
        $methodDoc['parameters'][$k]['Required'] = 'Y';
      }
    }

    if (!empty($this->next)) {
      $this->next->documentMethod($rClass, $rMethod, $methodDoc);
    }
  }
}
