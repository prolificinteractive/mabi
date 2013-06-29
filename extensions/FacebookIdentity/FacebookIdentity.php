<?php
namespace MABI\FacebookIdentity;

include_once __DIR__ . '/../../Extension.php';
include_once __DIR__ . '/../../DirectoryModelLoader.php';
include_once __DIR__ . '/../../DirectoryControllerLoader.php';

use MABI\DirectoryControllerLoader;
use MABI\DirectoryModelLoader;
use MABI\Extension;

class FacebookIdentity extends Extension {

  /**
   * @var bool
   */
  protected $facebookOnly;

  /**
   * @return boolean
   */
  public function getFacebookOnly() {
    return $this->facebookOnly;
  }

  public function __construct(\MABI\App $app, \MABI\Identity\Identity $identityExtension, $facebookOnly = FALSE) {
    parent::__construct($app);

    $this->facebookOnly = $facebookOnly;

    array_push($this->middlewareDirectories, __DIR__ . '/middleware');

    $this->addExtension($identityExtension);
    $this->setModelLoaders(array(
      new DirectoryModelLoader(__DIR__ . '/models', 'MABI\FacebookIdentity')
    ));
    $this->setControllerLoaders(array(
      new DirectoryControllerLoader(__DIR__ . '/controllers', $this, 'MABI\FacebookIdentity')
    ));
  }

  /**
   * Pulls the "Me" content from Facebook
   *
   * @param string $access_token The facebook connect access token
   *
   * @return mixed
   */
  public function getFBInfo($access_token) {
    // todo: see if call was erroneous and throw exceptions
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me?access_token=' . $access_token);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    // Get the response and close the channel.
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response);
  }
}
