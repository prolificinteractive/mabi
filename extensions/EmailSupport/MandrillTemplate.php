<?php

namespace MABI\EmailSupport;

include_once __DIR__ . '/DataTemplate.php';

class MandrillTemplate extends DataTemplate {

  function __construct($templateName, $subject, $data = array())
  {
    $this->templateName = $templateName;
    $this->subject = $subject;
    $this->data = $data;
  }

  public function getMessage()
  {
    throw new \Exception('MandrillTemplates cannot use this method');
  }

  /**
   * @return string
   */
  public function getSubject()
  {
    $this->subject;
  }

  /**
   * @return string
   */
  public function getTemplateName()
  {
    return $this->templateName;
  }
}