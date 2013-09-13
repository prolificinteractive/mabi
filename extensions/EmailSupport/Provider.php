<?php

namespace MABI\EmailSupport;;

interface Provider {

  function sendEmail($toEmail, $subject, $message);

}