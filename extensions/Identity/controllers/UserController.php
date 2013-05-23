<?php

namespace MABI\Identity;

use \MABI\RESTModelController;

/**
 * todo: docs
 *
 * @middleware \MABI\Middleware\RESTPostAndObjectOnlyAccess
 */
class UserController extends RESTModelController {

  function _restPostCollection() {
    // todo: set up passhash
  }

  function _restPutObject($id) {
    // todo: verify that id only valid for logged in id
  }
}
