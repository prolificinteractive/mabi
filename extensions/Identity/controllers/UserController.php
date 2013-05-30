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
class UserController extends RESTModelController {

  /**
   * @var User
   */
  protected $model;

  function _restPostCollection() {
    if(empty($this->model->password) || strlen($this->model->password) < 6) {
      $this->getApp()->getSlim()->response()->status(400);
      throw new Stop("Password must be at least 6 characters");
    }

    parent::_restPostCollection();

    $this->model->salt = uniqid(mt_rand(), true);
    $this->model->passHash = Identity::passHash($this->model->password, $this->model->salt);
    $this->model->save();
  }
}
