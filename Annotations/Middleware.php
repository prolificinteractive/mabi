<?php

namespace MABI\Annotations;

/**
 * Annotation that assigns a middleware automatically to a controller
 *
 * @author Photis Patriotis (ppatriotis@gmail.com)
 *
 * @Annotation
 * @Attributes({
 * @Attribute("value", required = true, type = "string")
 * })
 */
class Middleware {
  /**
   * @var string
   */
  protected $class;

  /**
   * @return string
   */
  public function getClass() {
    return $this->class;
  }

  /**
   * Annotation construct
   *
   * @param array $values
   *
   * @throws \BadMethodCallException
   */
  public function __construct(array $values) {
    $this->class = $values['value'];
  }
}