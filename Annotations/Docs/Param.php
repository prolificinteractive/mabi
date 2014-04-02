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
    if (empty($values['value'])) {
      throw AnnotationException::requiredError("value", "Docs\\Param", 'class...expects', "a(n) string.");
    }
    else {
      $this->value = $values['value'];
    }

    if (!empty($values['description'])) {
      $this->description = $values['description'];
    }

    $this->type     = empty($values['type']) ? 'string' : $values['type'];
    $this->location = empty($values['location']) ? 'body' : $values['location'];
    $this->required = empty($values['required']) ? FALSE : $values['required'];
  }
}