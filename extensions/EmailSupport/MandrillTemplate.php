<?php

namespace MABI\EmailSupport;

include_once __DIR__ . '/DataTemplate.php';

class MandrillTemplate extends DataTemplate {

  /**
   * MandrillTemplate constructor.
   *
   * @param string $templateName
   * @param string $subject
   * @param array $data
   * @param array $attachments
   */
  function __construct($templateName, $subject, $data = array(), $attachments = array()) {
    $this->templateName = $templateName;
    $this->subject      = $subject;
    $this->data         = $data;
    $this->attachments  = $attachments;
  }

  /**
   * @throws \Exception
   */
  public function getMessage() {
    throw new \Exception('MandrillTemplates cannot use this method');
  }

  /**
   * @return string
   */
  public function getSubject() {
    $this->subject;
  }

  /**
   * @return string
   */
  public function getTemplateName() {
    return $this->templateName;
  }

  /**
   * @return array
   */
  public function getAttachments() {
    return $this->attachments;
  }
}