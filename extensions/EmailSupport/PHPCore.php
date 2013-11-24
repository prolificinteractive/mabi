<?php

namespace MABI\EmailSupport;

class PHPCore implements Provider {

  /**
   * @var string
   */
  protected $headers = '';

  public function __construct($from) {
    $this->headers = "From: $from\r\n" .
                     "Reply-To: $from";
  }

  /**
   * @param $toEmail
   * @param TokenTemplate $template
   * @return bool|mixed
   */
  public function sendEmail($toEmail, $template) {
    return mail($toEmail, $template->getSubject(), $template->getTemplate(), $this->headers);
  }

}