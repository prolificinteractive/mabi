<?php

namespace MABI\EmailSupport;

class MandrillTemplate extends BaseTemplate {

  function __construct($templateName, $subject, $data = array())
  {
    $this->template = $templateName;
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
    return $this->template;
  }
}