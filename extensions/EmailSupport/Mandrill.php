<?php

namespace MABI\EmailSupport;

include_once __DIR__ . '/Provider.php';

class Mandrill implements Provider {

  /**
   * @var string
   */
  private $apiKey;

  /**
   * @var string
   */
  private $senderEmail;

  /**
   * @var string
   */
  private $senderName;


  public function __construct($apiKey, $senderEmail, $senderName) {
    $this->apiKey = $apiKey;
    $this->senderEmail = $senderEmail;
    $this->senderName = $senderName;
  }

  /**
   * @param $to string
   * @param $template BaseTemplate
   */
  public function sendEmail($to, $template) {
    if (get_class($template) == 'MABI\EmailSupport\MandrillTemplate') {
      return $this->sendEmailTemplateRequest(
        $to,
        $template->getSubject(),
        $template->getTemplate(),
        $template->getData());
    }
    else if (get_class($template) == 'MABI\EmailSupport\TokenTemplate') {
      return $this->sendEmailRequest($to, $template->getSubject(), $template->getTemplate());

    }
    else {
      throw new \Exception("Template must extend BaseTemplate");
    }

  }

  /**
   * @param $email
   * @param $subject
   * @param $templateName
   * @param $vars
   * @return mixed
   * @throws \Exception
   */
  private function sendEmailTemplateRequest($email, $subject, $templateName, $vars) {

    $url = 'https://mandrillapp.com/api/1.0/messages/send-template.json';

    $post_data = array(
      'key' => $this->apiKey,
      'template_name' => $templateName,
      'template_content' => array(),
      'message' => array(
        'subject' => $subject,
        'from_email' => $this->senderEmail,
        'from_name' => $this->senderName,
        'to' => array(
          array(
            'email' => $email
          )
        ),
        'merge' => true,
        'merge_vars' => array(
          array(
            "rcpt" => $email,
            "vars" => $vars
          )
        )
      )
    );

    $post_data = json_encode($post_data);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    $output = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $output = json_decode($output);
    if ($http_status == '200') {
      return $output[0];
    } else {
      if ($output->code = -1) {
        throw new \Exception('Mandrill failed to send the email template.');
      }
    }
  }

  /**
   * @param $toEmail
   * @param $subject
   * @param $message
   * @return mixed
   * @throws \Exception
   */
  private function sendEmailRequest($toEmail, $subject, $message) {

    $url = "https://mandrillapp.com/api/1.0/messages/send.json";

    $post_data = array (
      'key' => $this->apiKey,
      'message' => array(
        'html' => $message,
        'subject' => $subject,
        'from_email' => $this->senderEmail,
        'from_name' => $this->senderName,
        'to' => array(
          array(
            'email' => $toEmail
          )
        )
      )
    );

    $post_data = json_encode($post_data);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    $output = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $output = json_decode($output);
    if ($http_status == '200') {
      return $output[0];
    } else {
      if ($output->code = -1) {
        throw new \Exception('Mandrill failed to send the email');
      }
    }
  }
}