<?php

namespace MABI\EmailSupport;

include_once __DIR__ . '/DataTemplate.php';

class TokenTemplate extends DataTemplate {

  public $subject;

  public $templateName;

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
  public function getSubject()
  {
    return str_replace(array_keys($this->data), array_values($this->data), $this->subject);
  }


  /**
   * @return string
   */
  public function getMessage()
  {
    return str_replace(array_keys($this->data), array_values($this->data), $this->template);
  }
}