<?php

namespace MABI\EmailSupport;

include_once __DIR__ . '/Template.php';

Abstract class BaseTemplate implements Template {

  /**
   * @var string
   */
  protected $template;

  /**
   * @var string
   */
  protected $subject;

  /**
   * @var array
   */
  protected $data;

  /**
   * @param string $template
   */
  abstract function setTemplate($template);

  /**
   * @return string
   */
  abstract function getTemplate();


  /**
   * @param string $subject
   */
  public function setSubject($subject)
  {
    $this->subject = $subject;
  }

  /**
   * @return string
   */
  public function getSubject()
  {
    return $this->subject;
  }

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