<?php

namespace MABI\Middleware;

include_once dirname(__FILE__) . '/../DefaultApplicationModel.php';

use MABI\ReflectionHelper;

class SharedSecret extends \MABI\Middleware {
  /**
   * @var \MABI\Model
   */
  public $apiApplication = NULL;

  /**
   * Call
   *
   * Pulls out a anonymous sent from an http header
   *
   * Perform actions specific to this middleware and optionally
   * call the next downstream middleware.
   */
  public function call() {
    /**
     * Finds overridable Application Model (default -> Named Application -> annotated 'option ApplicationModel')
     * There can only be one.
     *
     * @var $applicationModelClass \MABI\Model
     */
    $applicationModelClass = '\MABI\DefaultApplicationModel';

    $mabi = $this->getController()->getApp();

    $modelClasses = $mabi->getModelClasses();
    foreach ($modelClasses as $modelClass) {
      if (ReflectionHelper::stripClassName($modelClass) == 'Application') {
        $applicationModelClass = $modelClass;
      }

      $rClass = new \ReflectionClass($modelClass);
      $modelOptions = ReflectionHelper::getDocProperty($rClass->getDocComment(), 'option');
      if (in_array('ApplicationModel', $modelOptions)) {
        $applicationModelClass = $modelClass;
        break;
      }
    }

    // Find the shared secret property (named sharedSecret -> annotated 'option SharedSecret')
    $rClass = new \ReflectionClass($applicationModelClass);
    $modelProps = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    $sharedSecretProp = 'sharedSecret';
    foreach ($modelProps as $modelProp) {
      $rProp = new \ReflectionProperty($applicationModelClass, $modelProp->name);
      $propOptions = ReflectionHelper::getDocProperty($rProp->getDocComment(), 'option');
      if (in_array('SharedSecret', $propOptions)) {
        $sharedSecretProp = $modelProp->name;
        break;
      }
    }

    $this->apiApplication = $applicationModelClass::init($mabi);
    if (!$this->apiApplication->findByField($sharedSecretProp, $mabi->getSlim()->request()->headers('shared-secret'))) {
      $this->apiApplication = FALSE;
    }
    $mabi->getSlim()->request()->apiApplication = $this->apiApplication;

    if (!empty($this->next)) {
      $this->next->call();
    }
  }

  public function documentMethod(\ReflectionClass $rClass, \ReflectionMethod $rMethod, array &$methodDoc) {
    parent::documentMethod($rClass, $rMethod, $methodDoc);

    $methodDoc['parameters'][] = array(
      'Name' => 'shared-secret',
      'Required' => 'N',
      'Type' => 'string',
      'Location' => 'header',
      'Description' => 'The guid that identifies which application is attempting to access this endpoint. Only
        the application itself and the internal API should be able to see this value, therefore, it should always
        be transmitted over HTTPs.'
    );
  }
}
