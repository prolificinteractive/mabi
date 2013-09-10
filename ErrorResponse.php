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
   * @param int|null $httpcode
   * @param int|null $code
   */
  function __construct($message, $httpcode = null, $code = null)
  {
    $this->message = $message;
    $this->httpcode = $httpcode;
    $this->code = $code;
  }

  public static function FromArray($array)
  {
    if (!isset($array['message'])) throw new \Exception('Invalid ErrorResponse Array. Must contain a message');
    return $newErrorResponse = new ErrorResponse($array['message'],
      isset($array['httpcode']) ? $array['httpcode'] : null,
      isset($array['code']) ? $array['code'] : null);
  }

  /**
   * @param int $code
   */
  public function setCode($code)
  {
    $this->code = $code;
  }

  /**
   * @return int
   */
  public function getCode()
  {
    return $this->code;
  }

  /**
   * @param int $httpcode
   */
  public function setHttpcode($httpcode)
  {
    $this->httpcode = $httpcode;
  }

  /**
   * @return int
   */
  public function getHttpcode()
  {
    return $this->httpcode;
  }

  /**
   * @param string $message
   */
  public function setMessage($message)
  {
    $this->message = $message;
  }

  /**
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }

  public function getFormattedMessage($replacementArray = null)
  {
    if (!empty($replacementArray)) {
      return str_replace(array_keys($replacementArray), array_values($replacementArray), $this->message);
    }
    return str_replace($this->replacementTokens, $this->replacementValues, $this->message);
  }
}