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
    empty($values['type']) ? $this->type = 'string' : $this->type = $values['type'];
    empty($values['location']) ? $this->location = 'body' : $this->location = $values['location'];
    empty($values['required']) ? $this->required = FALSE : $this->required = $values['required'];
    empty($values['value']) ? $this->value = FALSE : $this->value = $values['value'];
    empty($values['description']) ? $this->description = FALSE : $this->description = $values['description'];
  }
}