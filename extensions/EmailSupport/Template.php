<?php

namespace MABI\EmailSupport;

interface Template {


  public function getMessage();

  public function mergeData($data);

  /**
   * @return string
   */
  public function getSubject();
}

