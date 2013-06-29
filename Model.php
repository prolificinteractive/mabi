<?php

namespace MABI;

include_once __DIR__ . '/Inflector.php';
include_once __DIR__ . '/Utilities.php';

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
   * @field system
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

  public function getId() {
    return $this->{$this->idProperty};
  }

  /**
   * @return string
   */
  public function getIdProperty() {
    return $this->idProperty;
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

    $newModelObj->{$newModelObj->idProperty} = NULL;

    return $newModelObj;
  }

  /**
   * todo: docs
   *
   * @return Model[]
   */
  public function findAll() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $foundObjects = $dataConnection->findAll($this->table, $this->readFields);
    $foundModels = array();
    if (is_array($foundObjects)) {
      foreach ($foundObjects as $foundObject) {
        /**
         * @var $model \MABI\Model
         */
        $model = call_user_func($this->modelClass . '::init', $this->app);
        $model->loadParameters($foundObject);
        $foundModels[] = $model;
      }
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
      case '\DateTime':
        if (empty($result)) {
          $parameter = NULL;
        }
        else {
          $parameter = new \DateTime('@' . $result);
        }
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
   * @param $forceId string
   *
   * @throws \Exception
   */
  public function loadParameters($resultArray, $forceId = NULL) {
    $rClass = new \ReflectionClass($this);
    $rProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    foreach ($rProperties as $rProperty) {
      if (!array_key_exists($rProperty->name, $resultArray)) {
        continue;
      }
      // Pulls out the type following the pattern @var <TYPE> from the doc comments of the property
      $varDocs = ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'var');
      if (empty($varDocs)) {
        $this->{$rProperty->getName()} = $resultArray[$rProperty->getName()];
      }
      else {
        $type = $varDocs[0];
        $matches = array();

        if (preg_match('/(.*)\[\]/', $type, $matches)) {
          // If the type follows the list of type pattern (<TYPE>[]), an array will be generated and filled
          // with that type
          $type = $matches[1];
          $outArr = array();
          foreach ($resultArray[$rProperty->getName()] as $listResult) {
            $this->loadParameter($type, $parameter, $listResult);
            $outArr[] = $parameter;
          }
          $this->{$rProperty->getName()} = $outArr;
        }
        else {
          $this->loadParameter($type, $this->{$rProperty->getName()}, $resultArray[$rProperty->getName()]);
        }
      }
      unset($resultArray[$rProperty->getName()]);
    }

    if (!empty($resultArray[$this->idColumn])) {
      $this->{$this->idProperty} = $resultArray[$this->idColumn];
      unset($resultArray[$this->idColumn]);
    }
    if (isset($forceId)) {
      $this->{$this->idProperty} = $forceId;
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
    $this->loadParameters($result);
    return TRUE;
  }

  /**
   * todo: docs
   *
   * @param $fieldName string
   * @param $value string
   *
   * @return bool
   */
  public function findByField($fieldName, $value) {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $result = $dataConnection->findOneByField($fieldName, $value, $this->table, $this->readFields);
    if ($result == NULL) {
      return FALSE;
    }
    $this->loadParameters($result);
    return TRUE;
  }

  public function getPropertyArray($removeInternal = FALSE) {
    $rClass = new \ReflectionClass($this);

    $outArr = array();
    $rProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    foreach ($rProperties as $rProperty) {
      /*
       * Ignores writing any model property with 'external' option
       */
      if (!$removeInternal && in_array('external', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field'))) {
        continue;
      }
      if ($removeInternal && in_array('internal', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field'))) {
        continue;
      }
      if (in_array('system', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field'))) {
        continue;
      }

      if (!is_object($this->{$rProperty->getName()})) {
        $outArr[$rProperty->getName()] = $this->{$rProperty->getName()};
      }
      else {
        $propClass = new \ReflectionClass($this->{$rProperty->getName()});
        if ($propClass->isSubclassOf('\MABI\Model')) {
          /**
           * @var $subModel \MABI\Model
           */
          $subModel = $this->{$rProperty->getName()};
          $outArr[$rProperty->getName()] = $subModel->getPropertyArray($removeInternal);
        }
        elseif ($propClass->name == 'DateTime' || $propClass == '\DateTime') {
          /**
           * @var $date \DateTime
           */
          $date = $this->{$rProperty->getName()};
          $outArr[$rProperty->getName()] = $date->getTimestamp();
        }
      }
    }
    if (!empty($this->{$this->idProperty})) {
      if (!$removeInternal) {
        $outArr[$this->idColumn] = $this->{$this->idProperty};
      }
      else {
        $outArr[$this->idProperty] = $this->{$this->idProperty};
      }
    }
    if (!empty($this->_remainingReadResults) && !$removeInternal) {
      $outArr = array_merge($outArr, $this->_remainingReadResults);
    }

    return $outArr;
  }

  /**
   * todo: docs
   */
  public function insert() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $propArray = $dataConnection->insert($this->table, $this->getPropertyArray());
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
