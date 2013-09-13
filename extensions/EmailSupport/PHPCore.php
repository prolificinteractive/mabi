<?php

namespace MABI\EmailSupport;

class PHPCore implements Provider {

  public function sendEmail($toEmail, $subject, $message) {
    return mail($toEmail, $subject, $message);
  }

}