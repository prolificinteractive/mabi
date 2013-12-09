<?php

namespace MABI;

class ErrorResponse
{

  /**
   * @var string
   */
  protected $message;

  /**
   * @var int
   */
  protected $httpcode;

  /**
   * @var int
   */
  protected $code;

  /**
   * @var array
   */
  protected $replacementTokens = array();

  /**
   * @var array
   */
  protected $replacementValues = array();

  /**
   * @param string $message
   * @param int $httpcode
   * @param int|null $code
   */
  function __construct($message, $httpcode, $code = null)
  {
    $this->message = $message;
    $this->httpcode = $httpcode;
    $this->code = $code;
  }

  public static function FromArray($array)
  {
    if (!isset($array['message'])) throw new \Exception('Invalid ErrorResponse Array. Must contain a message');
    return $newErrorResponse = new ErrorResponse($array['message'],
      $array['httpcode'],
      isset($array['code']) ? $array['code'] : null);
  }

  /**
   * @return int
   */
  public function getCode()
  {
    return $this->code;
  }

  /**
   * @return int
   */
  public function getHttpcode()
  {
    return $this->httpcode;
  }

  /**
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }

  public function getFormattedMessage($replacementArray = array())
  {
    if (!empty($replacementArray)) {
      return str_replace(array_keys($replacementArray), array_values($replacementArray), $this->getMessage());
    }
    return str_replace($this->replacementTokens, $this->replacementValues, $this->getMessage());
  }
}