<?php

namespace MABI\Identity\Middleware;

use MABI\Middleware;
use MABI\Identity\Session;
use MABI\Identity\User;

include_once __DIR__ . '/../../../Middleware.php';

class SessionHeader extends Middleware {
  /**
   * @var \MABI\Identity\Session
   */
  public $session = NULL;

  /**
   * Call
   *
   * Pulls out a anonymous sent from an http header
   *
   * Perform actions specific to this middleware and optionally
   * call the next downstream middleware.
   */
  public function call() {
    $sessionId = $this->getApp()->getRequest()->headers('SESSION');

    $foundSession = Session::init($this->getApp());
    if($foundSession->findById($sessionId)) {
      $this->session = $foundSession;
      $this->getApp()->getRequest()->session = $this->session;
      $now = new \DateTime('now');
      $this->session->lastAccessed = $now;

      $user = User::init($this->getApp());
      $user->findById($this->session->userId);
      $user->lastAccessed = $now;
      $user->save();
    }

    if (!empty($this->next)) {
      $this->next->call();
    }
  }

  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
    parent::documentMethod($rClass, $rMethod, $methodDoc);

    $methodDoc['parameters'][] = array(
      'Name' => 'SESSION',
      'Required' => 'N',
      'Type' => 'string',
      'Location' => 'header',
      'Description' => 'A guid that identifies the current logged in session (the session id when you create a session)'
    );

    if (!empty($this->next)) {
      $this->next->documentMethod($rClass, $rMethod, $methodDoc);
    }
  }
}
