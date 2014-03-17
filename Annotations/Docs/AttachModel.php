<?php

namespace MABI\Annotations\Docs;

/**
 * Annotation
 *
 * @author Photis Patriotis (ppatriotis@gmail.com)
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class AttachModel {
  /**
   * @var string
   * @Required
   */
  public $value;
}