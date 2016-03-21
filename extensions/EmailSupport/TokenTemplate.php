<?php

namespace MABI\EmailSupport;

include_once __DIR__ . '/DataTemplate.php';

class TokenTemplate extends DataTemplate {

  /**
   * @var string
   */
  public $subject;

  /**
   * @var string
   */
  public $templateName;

  /**
   * @var array
   */
  public $attachments = array();

  /**
   * TokenTemplate constructor.
   *
   * @param string $template
   * @param string $subject
   * @param array  $data
   * @param array  $attachments
   */
  function __construct($template, $subject, $data = array(), $attachments = array()) {
    $this->template    = $template;
    $this->subject     = $subject;
    $this->data        = $data;
    $this->attachments = $attachments;
  }

  /**
   * @param string $template
   */
  public function setTemplate($template) {
    $this->template = $template;
  }

  /**
   * @return string
   */
  public function getSubject() {
    return str_replace(array_keys($this->data), array_values($this->data), $this->subject);
  }

  /**
   * @return string
   */
  public function getMessage() {
    return str_replace(array_keys($this->data), array_values($this->data), $this->template);
  }

  /**
   * @return array
   */
  public function getAttachments() {
    return $this->attachments;
  }
}