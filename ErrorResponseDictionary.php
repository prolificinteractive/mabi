<?php

namespace MABI;

class ErrorResponseDictionary {
  /**
   * @var ErrorResponse[]
   */
  protected $errorResponses = array();

  /**
   * Should be in the format (all required):
   *   static $SAMPLE_ERROR_KEY = array(
   *     'message' => 'sample error message',
   *     'httpcode' => '401',
   *     'code' => 1); // optional
   */
  function __construct() {
    $rClass = new \ReflectionClass(get_called_class());
    $rProperties = $rClass->getProperties(\ReflectionProperty::IS_STATIC);
    $defaultProps = $rClass->getDefaultProperties();
    foreach ($rProperties as $rProperty) {
      $ignoreAnnotation = ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'ignore');
      if (!empty($ignoreAnnotation) || !is_array($defaultProps[$rProperty->getName()]) ||
        empty($defaultProps[$rProperty->getName()])
      ) {
        continue;
      }

      $propertyKeys = array_keys($defaultProps[$rProperty->getName()]);
      $key = $propertyKeys[0];
      $errorResponseArray = $defaultProps[$rProperty->getName()][$key];
      if (empty($errorResponseArray['message']) || empty($errorResponseArray['httpcode'])) {
        continue;
      }

      $this->errorResponses[$key] = ErrorResponse::FromArray($errorResponseArray);
    }
  }

  /**
   * Creates or overrides error responses with an initialization array in mass
   *
   * @param $initArray
   */
  public function overrideErrorResponses(ErrorResponseDictionary $overridingDictionary) {
    foreach ($overridingDictionary->errorResponses as $key => $errorResponse) {
      $this->errorResponses[$key] = $errorResponse;
    }
  }

  public function overrideErrorResponse($key, ErrorResponse $errorResponse) {
    $this->errorResponses[$key] = $errorResponse;
  }

  /**
   * todo: docs
   *
   * @param $key
   *
   * @return ErrorResponse|null
   */
  public function getErrorResponse($key) {
    if (empty($this->errorResponses[$key])) {
      return NULL;
    }

    return $this->errorResponses[$key];
  }
}