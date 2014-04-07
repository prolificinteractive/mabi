<?php

namespace MABI\Annotations\Docs;

/**
 * Annotation
 *
 * @author Photis Patriotis (ppatriotis@gmail.com)
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("value",  required = true,  type = "string"),
 *   @Attribute("type", required = false, type = "string"),
 *   @Attribute("location", required = false, type = "string"),
 *   @Attribute("required", required = false, type = "boolean"),
 *   @Attribute("description", required = false, type = "string")
 * })
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
    $this->value = $values['value'];
  }

  public function setProperty($property, $property_value, $available) {
    if (!in_array($property_value, $available)) {
      throw \Doctrine\Common\Annotations\AnnotationException::enumeratorError($property, 'Docs\\Param', 'a method', $available, $property_value);
    }
    else {
      $this->{$property} = $property_value;
    }
  }
}