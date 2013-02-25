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
   * @var array
   */
  protected $remainingReadResults;

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
      $newModelObj->table = strtolower(Inflector::pluralize(ReflectionHelper::stripClassName($modelClass)));
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

  /**
   * Sets a parameter based on its type
   *
   * @param $type
   * @param $parameter
   * @param $result
   *
   * @throws \Exception
   */
  protected function loadParameter($type, &$parameter, $result) {
    switch ($type) {
      case 'string':
        $parameter = $result;
        break;
      case 'int':
        $parameter = intval($result);
        break;
      case 'bool':
        $parameter = $result == TRUE;
        break;
      case 'float':
        $parameter = floatval($result);
        break;
      case 'DateTime':
        $parameter = new \DateTime('@' . $result);
        break;
      case '':
      case 'array':
        $parameter = $result;
        break;
      default:
        try {
          $rClass = new \ReflectionClass($type);
          if ($rClass->isSubclassOf('\MABI\Model')) {
            /**
             * @var $model \MABI\Model
             */
            $model = call_user_func($type . '::init', $this->app);
            $model->loadParameters($result);
            $parameter = $model;
          }
          else {
            throw New \Exception('Class ' . $type . ' does not derive from \MABI\Model');
          }
        } catch (\ReflectionException $ex) {
          $parameter = $result;
        }
    }
  }

  /**
   * Loads parameters from a PHP database into the model object using reflection
   *
   * @param $resultArray array
   *
   * @throws \Exception
   */
  protected function loadParameters($resultArray) {
    $rClass = new \ReflectionClass($this);
    $myProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    foreach ($myProperties as $property) {
      $rProp = new \ReflectionProperty($this, $property->name);
      $propComment = $rProp->getDocComment();
      $matches = array();
      // Pulls out the type following the pattern @var <TYPE> from the doc comments of the property
      if (!preg_match('/\@var\s(.*)\s/', $propComment, $matches)) {
        $this->{$property->name} = $resultArray[$property->name];
      }
      else {
        $type = $matches[1];
        $matches = array();

        if (preg_match('/(.*)\[\]/', $type, $matches)) {
          // If the type follows the list of type pattern (<TYPE>[]), an array will be generated and filled
          // with that type
          $type = $matches[1];
          $outArr = array();
          foreach ($resultArray[$property->name] as $listResult) {
            $this->loadParameter($type, $parameter, $listResult);
            $outArr[] = $parameter;
          }
          $this->{$property->name} = $outArr;
        }
        else {
          $this->loadParameter($type, $this->{$property->name}, $resultArray[$property->name]);
        }
      }
      unset($resultArray[$property->name]);
    }
    $this->remainingReadResults = $resultArray;
  }

  /**
   * todo: docs
   *
   * @param $id
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
