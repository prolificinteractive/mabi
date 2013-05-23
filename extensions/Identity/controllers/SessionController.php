<?php

namespace MABI\Identity;

use \MABI\RESTModelController;

/**
 * todo: docs
 *
 * todo: add middleware where only post is allowed as anonymous
 * @middleware \MABI\Middleware\RESTPostAndObjectOnlyAccess
 */
class SessionController extends RESTModelController {

  function _restPostCollection() {
    // todo: implement. verify user/password and then create session
  }

  function _restPutObject($id) {
    // todo: verify that id only valid for logged in id
  }
}
