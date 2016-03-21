<?php

namespace MABI\EmailSupport;

interface Template {

  /**
   * @return mixed
   */
  public function getMessage();

  /**
   * @param $data
   *
   * @return mixed
   */
  public function mergeData($data);

  /**
   * @return string
   */
  public function getSubject();

  /**
   * @return array
   */
  public function getAttachments();
}

