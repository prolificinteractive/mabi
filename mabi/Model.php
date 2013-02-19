<?php

namespace MABI;

include_once dirname(__FILE__) . '/Inflector.php';
include_once dirname(__FILE__) . '/Utilities.php';

/**
 * todo: docs
 */
class Model {

  /**
   * @var App
   */
  protected $app;

  /**
   * @var string
   */
  protected $modelClass;

  /**
   * @var string
   */
  protected $connection = 'default';

  /**
   * @var string
   */
  protected $table = NULL;

  protected $readAccess;
  protected $writeAccess;

  /**
   * @param string $connection
   */
  public function setConnection($connection) {
    $this->connection = $connection;
  }

  /**
   * @return string
   */
  public function getConnection() {
    return $this->connection;
  }

  /**
   * @param string $table
   */
  public function setTable($table) {
    $this->table = $table;
  }

  /**
   * @return string
   */
  protected function getTable() {
    return $this->table;
  }

  /**
   * todo: docs
   *
   * @param $app App
   *
   * @return Model
   */
  public static function init($app) {
    $modelClass = get_called_class();

    $newModelObj = new $modelClass();
    $newModelObj->modelClass = get_called_class();
    $newModelObj->app = $app;
    if (empty($newModelObj->table)) {
      $newModelObj->table = Inflector::tableize(ReflectionHelper::stripClassName($modelClass));
    }

    // todo: implement
    return $newModelObj;
  }

  /**
   * todo: docs
   *
   * @param $values Array|NULL
   */
  public function insert($values = NULL) {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $dataConnection->insert($this->table, $values);
  }

  public function findAll() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $allData = $dataConnection->findAll($this->table);
  }

  /**
   * todo: docs
   */
  public function clearAll() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    return $dataConnection->clearAll($this->table);
  }
}
