<?php

namespace MABI\EmailSupport;

include_once __DIR__ . '/Template.php';


/**
 * Class DataTemplate
 * @package MABI\EmailSupport
 *
 * Adds the necessary properties and methods for a template class that takes an array of
 * values to replace tokens in a template string.
 */
abstract class DataTemplate implements Template {

  /**
   * @var array
   */
  protected $data = array();

  /**
   * @param $data
   */
  public function mergeData($data) {
    $this->data = array_merge($this->data, $data);
  }

  /**
   * @return array
   */
  public function getData() {
    return $this->data;
  }

  public function clearData() {
    $this->data = array();
  }
}