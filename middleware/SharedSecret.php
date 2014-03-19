<?php

namespace MABI\Middleware;

include_once __DIR__ . '/../DefaultApplicationModel.php';
include_once __DIR__ . '/../Middleware.php';

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

    $mabi = $this->getApp();

    $modelClasses = $mabi->getModelClasses();
    $annotationReader = $this->getApp()->getAnnotationReader();
    foreach ($modelClasses as $modelClass) {
      if (ReflectionHelper::stripClassName($modelClass) == 'Application') {
        $applicationModelClass = $modelClass;
      }

      $rClass = new \ReflectionClass($modelClass);
      if ($annotationReader->getClassAnnotation($rClass, 'MABI\Annotations\Model\Application')) {
        $applicationModelClass = $modelClass;
        break;
      }
    }

    // Find the shared secret property (named sharedSecret or annotated 'field SharedSecret')
    $rClass = new \ReflectionClass($applicationModelClass);
    $modelProps = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    $sharedSecretProp = 'sharedSecret';
    foreach ($modelProps as $modelProp) {
      $rProp = new \ReflectionProperty($applicationModelClass, $modelProp->name);
      if ($annotationReader->getPropertyAnnotation($rProp, 'MABI\Annotations\Field\SharedSecret')) {
        $sharedSecretProp = $modelProp->name;
        break;
      }
    }

    $this->apiApplication = $applicationModelClass::init($mabi);
    if (!$this->apiApplication->findByField($sharedSecretProp, $mabi->getRequest()->headers('SHARED-SECRET'))) {
      $this->apiApplication = FALSE;
    }
    $mabi->getRequest()->apiApplication = $this->apiApplication;

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

    if (!empty($this->next)) {
      $this->next->documentMethod($rClass, $rMethod, $methodDoc);
    }
  }
}
