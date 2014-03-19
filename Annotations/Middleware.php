<?php

namespace MABI\Annotations;

/**
 * Annotation that assigns a middleware automatically to a controller
 *
 * @author Photis Patriotis (ppatriotis@gmail.com)
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class Middleware {
  /**
   * @var string
   * @Required
   */
  public $value;
}