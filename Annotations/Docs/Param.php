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
    if (!empty($values['description'])) {
      $this->description = $values['description'];
    }

    if (empty($values['type'])) {
      $this->type = 'string';
    }
    else {
      $available = array('string', 'int', 'float', 'bool', 'file');
      $this->setProperty('type', $values['type'], $available);
    }

    if (empty($values['location'])) {
      $this->location = 'body';
    }
    else {
      $available = array('url', 'body', 'header', 'query');
      $this->setProperty('location', $values['location'], $available);
    }

    $this->required = empty($values['required']) ? FALSE : $values['required'];
  }

  public function setProperty($property, $property_value, $available) {
    if (!in_array($property_value, $available)) {
      throw AnnotationException::enumeratorError('type', 'Docs\\Param', 'class...', $available, $property_value);
    }
    else {
      $this->{$property} = $property_value;
    }
  }
}