<?php

namespace MABI\Annotations\Docs;

/**
 * Annotation
 *
 * @author Photis Patriotis (ppatriotis@gmail.com)
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class Param {
  /**
   * @var string
   * @Required
   */
  public $value;

  /**
   * @var string
   * @Enum({'string','int','float','boolean','bool','file'})
   */
  public $type;

  /**
   * @var string
   * @Enum({'url','body','header','query'})
   */
  public $location;

  /**
   * @var boolean
   */
  public $required;

  /**
   * @var string
   */
  public $description;

  function __construct(array $values) {
    if(empty($values['type'])) {
      $this->type = 'string';
    }
    if(empty($values['location'])) {
      $this->location = 'body';
    }
    if(empty($values['required'])) {
      $this->required = false;
    }
  }
}