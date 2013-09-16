<?php

namespace MABI\EmailSupport;

include_once __DIR__ . '/BaseTemplate.php';

class TokenTemplate extends BaseTemplate {

  /**
   * @var array
   */
  protected $replaceTokens = array();

  function __construct($template, $subject, $data = array())
  {
    $this->template = $template;
    $this->subject = $subject;
    $this->data = $data;
  }

  /**
   * @param string $template
   */
  public function setTemplate($template)
  {
    $this->template = $template;
  }

  /**
   * @return string
   */
  public function getTemplate()
  {
    return str_replace(array_keys($this->data), array_values($this->data), $this->template);
  }
}