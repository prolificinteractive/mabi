<?php

namespace MABI\EmailSupport;

class PHPCore implements Provider {

  public function sendEmail($to, $subject, $message) {
    return mail($to, $subject, $message);
  }

}