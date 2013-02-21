<?php

namespace MABI;

include_once dirname(__FILE__) . '/ControllerLoader.php';


/**
 * automatically generates RESTful controllers
 * GET      /<model>          get all models by id
 * POST     /<model>          creates a new model
 * PUT      /<model>          bulk creates full model collection
 * DELETE   /<model>          deletes all models
 * GET      /<model>/<id>     gets one model's full details
 * PUT      /<model>/<id>     updates the model
 * DELETE   /<model>/<id>     deletes the model
 *
 * These functions will be restricted by AccessControl
 */
class ModelGeneratedControllerLoader extends ControllerLoader {

}