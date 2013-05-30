<?php

namespace MABI\Identity;

use \MABI\RESTModelController;
use Slim\Exception\Stop;

/**
 * todo: docs
 *
 * @middleware \MABI\Middleware\RESTPostAndObjectOnlyAccess
 * @middleware \MABI\Identity\Middleware\SessionHeader
 * @middleware \MABI\Identity\Middleware\RESTOwnerOnlyAccess
 */
class SessionController extends RESTModelController {

  /**
   * @var Session
   */
  protected $model;

  function _restPostCollection() {
    if(empty($this->model->password) || empty($this->model->email)) {
      $this->getApp()->getSlim()->response()->status(400);
      throw new Stop("Email and Password are required to create a section");
    }

    /**
     * @var $user User
     */
    $user = User::init($this->getApp());
    $user->findByField('email', $this->model->email);

    if($user->passHash != Identity::passHash($this->model->password, $user->salt)) {
      $this->getApp()->getSlim()->response()->status(400);
      throw new Stop("Email and password are required to create a section");
    }

    parent::_restPostCollection();
  }
}
