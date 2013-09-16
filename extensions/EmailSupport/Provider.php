<?php

namespace MABI\EmailSupport;;

interface Provider {

  /**
   * @param $toEmail string
   * @param $template Template
   * @return mixed
   */
  function sendEmail($toEmail, $template);

}