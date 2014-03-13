<?php

namespace MABI\Testing;

use MABI\App;
use mabiTesting\Errors;

include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../App.php';
include_once __DIR__ . '/MockDataConnection.php';
include_once __DIR__ . '/TestApp/Errors.php';

class TableDefinition {
  /**
   * @var string
   */
  protected $queryField;

  /**
   * @var string
   */
  protected $queryValue;

  /**
   * @var array
   */
  protected $returnValue;

  function __construct($queryField, $queryValue, $returnValue) {
    $this->queryField = $queryField;
    $this->queryValue = $queryValue;
    $this->returnValue = $returnValue;
  }

  /**
   * @return string
   */
  public function getQueryField() {
    return $this->queryField;
  }

  /**
   * @return string
   */
  public function getQueryValue() {
    return $this->queryValue;
  }

  /**
   * @return array
   */
  public function getReturnValue() {
    return $this->returnValue;
  }
}

class AppTestCase extends \PHPUnit_Framework_TestCase {

  /**
   * @var \MABI\App
   */
  protected $app;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $dataConnectionMock;

  private function timeOverrides($namespaces = array()) {
    static $overridden = FALSE;

    if (!$overridden) {
      $returnTime = time();
      foreach ($namespaces as $namespace) {
        eval("namespace $namespace; function time() { return $returnTime; }");
      }
      $overridden = TRUE;
    }
  }

  public function setUpApp($env = array(), $withCache = false) {
    $this->timeOverrides(array(
      'MABI\FacebookIdentity\Testing',
      'Slim\Http',
      'MABI\Identity',
      'MABI\Identity\Middleware',
      'MABI\Identity\Testing',
    ));
    \Slim\Environment::mock($env);
    $this->app = new App();

    $this->dataConnectionMock = $this->getMock('\MABI\Testing\MockDataConnection',
      array(
        'findOneByField',
        'query',
        'insert',
        'save',
        'deleteByField',
        'clearAll',
        'getNewId',
        'findAll',
        'findAllByField',
        'count'
      )
    );

    $this->app->addDataConnection('default', $this->dataConnectionMock);

    if($withCache) {
      $this->app->addCacheRepository('system', 'file', array('path' => 'TestApp/cache'));
    }
    $this->app->getErrorResponseDictionary()->overrideErrorResponses(new Errors());
  }

  protected function returnTableValue($field, $value, TableDefinition $tableDefinition) {
    $this->assertEquals($field, $tableDefinition->getQueryField());
    $this->assertEquals($value, $tableDefinition->getQueryValue());
    return $tableDefinition->getReturnValue();
  }

  /**
   * @param TableDefinition[] $tableDefinitions
   * @param $field
   * @param $value
   * @param $table
   *
   * @return mixed
   */
  protected function findOneByFieldCallback($tableDefinitions, $field, $value, $table) {
    foreach ($tableDefinitions as $tdTable => $tableDefinition) {
      if ($tdTable == $table) {
        return $this->returnTableValue($field, $value, $tableDefinition);
      }
    }

    $this->fail("Table '$table' should not be called");
    return NULL;
  }
}