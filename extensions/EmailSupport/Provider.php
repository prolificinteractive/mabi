<?php

namespace MABI\EmailSupport;;

interface Provider {

  function sendEmail($to, $subject, $message);

}