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
  protected $idColumn;

  /**
   * @var string
   */
  protected $idProperty = 'id';

  /**
   * @var string
   */
  protected $table = NULL;

  protected $readAccess;
  protected $writeAccess;

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

    /**
     * @var $newModelObj Model
     */
    $newModelObj = new $modelClass();
    $newModelObj->modelClass = get_called_class();
    $newModelObj->app = $app;
    if (empty($newModelObj->table)) {
      $newModelObj->table = Inflector::tableize(ReflectionHelper::stripClassName($modelClass));
    }

    if (empty($newModelObj->idColumn)) {
      $newModelObj->idColumn = $newModelObj->app->getDataConnection($newModelObj->connection)->getDefaultIdColumn();
    }

    // todo: implement
    return $newModelObj;
  }

  /**
   * todo: docs
   *
   * @return Model[]
   */
  public function findAll() {
    // todo: implement
    $dataConnection = $this->app->getDataConnection($this->connection);
    $dataConnection->findAll($this->table);

    return array();
  }

  protected function loadParameters($resultArray) {
    $rClass = new \ReflectionClass($this);
    $myProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    foreach ($myProperties as $property) {
      $rProp = new \ReflectionProperty($this, $property->name);
      $propComment = $rProp->getDocComment();
      $matches = array();
      preg_match('/\@var\s(.*)\s/', $propComment, $matches);
      if (empty($matches)) {
        $this->{$property->name} = $resultArray[$property->name];
      }
      else {
        $type = $matches[1];
        switch ($type) {
          case 'string':
            break;
          case 'int':
            break;
          case 'DateTime':
            break;
          default:
            $rClass = new \ReflectionClass($type);
            if ($rClass->isSubclassOf('\MABI\Model')) {
              var_dump('recognized model');
            }
            else {
              throw New \Exception('Property ' . $property->name . ' does not derive from \MABI\Model');
            }
        }
      }
    }
  }

// php -r "\$matches = array(); preg_match('@var abcd ',\$matches);"
  /**
   * todo: docs
   *
   * @return Model
   */
  public function findById($id) {
    // todo: implement
    $dataConnection = $this->app->getDataConnection($this->connection);
    $result = $dataConnection->findOneByField($this->idColumn, $id, $this->table);
    $this->{$this->idProperty} = $id;
    $this->loadParameters($result);
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

  /**
   * todo: docs
   */
  public function clearAll() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    return $dataConnection->clearAll($this->table);
  }
}
