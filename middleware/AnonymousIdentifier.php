<?php

namespace MABI\Middleware;

class AnonymousIdentifier extends \MABI\Middleware {
  public $anonymousId = NULL;

  /**
   * Call
   *
   * Pulls out a anonymous sent from an http header
   *
   * Perform actions specific to this middleware and optionally
   * call the next downstream middleware.
   */
  public function call() {
    $this->anonymousId = $this->getController()->getApp()->getSlim()->request()->headers('anonuuid');
    $this->getController()->getApp()->getSlim()->request()->anonymousId = $this->anonymousId;
    if (!empty($this->next)) {
      $this->next->call();
    }
  }

  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
    parent::documentMethod($rClass, $rMethod, $methodDoc);

    $methodDoc['parameters'][] = array(
      'Name' => 'anonuuid',
      'Required' => 'N',
      'Type' => 'string',
      'Location' => 'header',
      'Description' => 'A guid that can be passed in to identify an anonymous user'
    );
  }
}
