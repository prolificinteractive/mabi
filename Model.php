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
   * @options system
   * @var array
   */
  public $_remainingReadResults;

  /**
   * @var array
   */
  protected $readFields = array();

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
    $foundObjects = $dataConnection->findAll($this->table, $this->readFields);
    $foundModels = array();
    foreach ($foundObjects as $foundObject) {
      /**
       * @var $model \MABI\Model
       */
      $model = call_user_func($this->modelClass . '::init', $this->app);
      $model->loadParameters($foundObject);
      $foundModels[] = $model;
    }
    return $foundModels;
  }

  /**
   * @param $query array
   *
   * @return Model[]
   */
  public function query($query) {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $foundObjects = $dataConnection->query($this->table, $query);
    $foundModels = array();
    foreach ($foundObjects as $foundObject) {
      /**
       * @var $model \MABI\Model
       */
      $model = call_user_func($this->modelClass . '::init', $this->app);
      $model->loadParameters($foundObject);
      $foundModels[] = $model;
    }
    return $foundModels;
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
  public function loadParameters($resultArray) {
    $rClass = new \ReflectionClass($this);
    $myProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    foreach ($myProperties as $property) {
      if (!array_key_exists($property->name, $resultArray)) {
        continue;
      }
      $rProp = new \ReflectionProperty($this, $property->name);
      $propComment = $rProp->getDocComment();
      // Pulls out the type following the pattern @var <TYPE> from the doc comments of the property
      $varDocs = ReflectionHelper::getDocProperty($propComment, 'var');
      if (empty($varDocs)) {
        $this->{$property->getName()} = $resultArray[$property->getName()];
      }
      else {
        $type = $varDocs[0];
        $matches = array();

        if (preg_match('/(.*)\[\]/', $type, $matches)) {
          // If the type follows the list of type pattern (<TYPE>[]), an array will be generated and filled
          // with that type
          $type = $matches[1];
          $outArr = array();
          foreach ($resultArray[$property->getName()] as $listResult) {
            $this->loadParameter($type, $parameter, $listResult);
            $outArr[] = $parameter;
          }
          $this->{$property->getName()} = $outArr;
        }
        else {
          $this->loadParameter($type, $this->{$property->getName()}, $resultArray[$property->getName()]);
        }
      }
      unset($resultArray[$property->getName()]);
    }

    if (!empty($resultArray[$this->idColumn])) {
      $this->{$this->idProperty} = $resultArray[$this->idColumn];
      unset($resultArray[$this->idColumn]);
    }

    $this->_remainingReadResults = $resultArray;
  }

  /**
   * todo: docs
   *
   * @param $id
   *
   * @return bool
   */
  public function findById($id) {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $result = $dataConnection->findOneByField($this->idColumn, $id, $this->table, $this->readFields);
    if ($result == NULL) {
      return FALSE;
    }
    $this->{$this->idProperty} = $id;
    $this->loadParameters($result);
    return TRUE;
  }

  protected function getPropertyArray($removeInternal = FALSE) {
    $rClass = new \ReflectionClass($this);

    $outArr = array();
    $myProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    foreach ($myProperties as $property) {
      /*
       * Ignores writing any model property with 'external' option
       */
      if (!$removeInternal && in_array('external', ReflectionHelper::getDocProperty($property->getDocComment(), 'options'))) {
        continue;
      }
      if ($removeInternal && in_array('internal', ReflectionHelper::getDocProperty($property->getDocComment(), 'options'))) {
        continue;
      }
      if (in_array('system', ReflectionHelper::getDocProperty($property->getDocComment(), 'options'))) {
        continue;
      }

      if (!is_object($this->{$property->getName()})) {
        $outArr[$property->getName()] = $this->{$property->getName()};
      }
      else {
        $propClass = new \ReflectionClass($this->{$property->getName()});
        if ($propClass->isSubclassOf('\MABI\Model')) {
          /**
           * @var $subModel \MABI\Model
           */
          $subModel = $this->{$property->getName()};
          $outArr[$property->getName()] = $subModel->getPropertyArray();
        }
      }
    }
    if (!empty($this->{$this->idProperty})) {
      $outArr[$this->idColumn] = $this->{$this->idProperty};
    }
    if (!empty($this->_remainingReadResults)) {
      $outArr = array_merge($outArr, $this->_remainingReadResults);
    }

    return $outArr;
  }

  /**
   * todo: docs
   */
  public function insert() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $propArray = $this->getPropertyArray();
    $dataConnection->insert($this->table, $propArray);
    $this->loadParameters($propArray);
  }

  /**
   * todo: docs
   */
  public function save() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $propArray = $this->getPropertyArray();
    $dataConnection->save($this->table, $propArray, $this->idColumn, $this->{$this->idProperty});
    $this->loadParameters($propArray);
  }

  /**
   * todo: docs
   */
  public function delete() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $dataConnection->deleteByField($this->idColumn, $this->{$this->idProperty}, $this->table);
  }

  /**
   * todo: docs
   */
  public function clearAll() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    return $dataConnection->clearAll($this->table);
  }

  public function outputJSON() {
    return json_encode($this->getPropertyArray(TRUE));
  }
}
