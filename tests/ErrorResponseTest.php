<?php

namespace MABI\Testing;

include_once __DIR__ . '/middleware/MiddlewareTestCase.php';
include_once __DIR__ . '/../middleware/APIApplicationOnlyAccess.php';
include_once __DIR__ . '/../middleware/SharedSecret.php';

class ErrorResponseTest extends MiddlewareTestCase {

  /**
   * @var \mabiTesting\ModelA
   */
  protected $insertedModel;

  public function testCustomError() {
    $this->setUpApp(array('PATH_INFO' => '/justa/customerror'));

    $this->app->call();

    $this->assertJson($this->app->getResponse()->body());
    $response = json_decode($this->app->getResponse()->body());
    $this->assertEquals($response->error->code, 1);
    $this->assertEquals($response->error->message, "New test error with a replacement string");
    $this->assertEquals(401, $this->app->getResponse()->status());
  }

  public function testCustomError2() {
    $this->setUpApp(array('PATH_INFO' => '/justa/customerror2'));

    $this->app->call();

    $this->assertJson($this->app->getResponse()->body());
    $response = json_decode($this->app->getResponse()->body());
    $this->assertEquals($response->error->code, 1);
    $this->assertEquals($response->error->message, "Test error2");
    $this->assertEquals(401, $this->app->getResponse()->status());
  }

  public function testCustomError3() {
    $this->setUpApp(array('PATH_INFO' => '/justa/customerror3'));

    $this->app->call();

    $this->assertJson($this->app->getResponse()->body());
    $response = json_decode($this->app->getResponse()->body());
    $this->assertEquals($response->error->code, 1);
    $this->assertEquals($response->error->message, "New test error with a replacement string");
    $this->assertEquals(401, $this->app->getResponse()->status());
  }

  public function testErrorOverride() {
    $middleware = new \MABI\Middleware\SharedSecret();
    $middleware2 = new \MABI\Middleware\APIApplicationOnlyAccess();
    $this->setUpApp(array('PATH_INFO' => '/justa/testfunc'), 'mabiTesting\JustAController',
      array($middleware, $middleware2));

    $this->app->call();

    $this->assertJson($this->app->getResponse()->body());
    $response = json_decode($this->app->getResponse()->body());
    $this->assertEquals($response->error->code, 1007);
    $this->assertEquals($response->error->message, "Why don't you just get out of here, ok?");
    $this->assertEquals(401, $this->app->getResponse()->status());
  }
}