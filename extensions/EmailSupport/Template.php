<?php

namespace MABI\EmailSupport;

interface Template {
  public function getMessage($data);
}

class TokenTemplate implements Template {

  /**
   * @var string
   */
  protected $template;

  /**
   * @var array
   */
  protected $replaceTokens = array();

  function __construct($tokenizedTemplate, $replaceTokens = array())
  {
    $this->template = $tokenizedTemplate;
    $this->replaceTokens = $replaceTokens;
  }

  /**
   * @param array $replaceTokens
   */
  public function setReplaceTokens($replaceTokens)
  {
    $this->replaceTokens = $replaceTokens;
  }

  /**
   * @return array
   */
  public function getReplaceTokens()
  {
    return $this->replaceTokens;
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

  public function getMessage($data)
  {
    return str_replace(
      array_merge(array_keys($this->replaceTokens), array_keys($data)),
      array_merge(array_values($this->replaceTokens), array_values($data)),
      $this->template);
  }
}