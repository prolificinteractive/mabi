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
  protected $idProperty;

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

  public function setId($id) {
    $this->{$this->idProperty} = $id;
  }

  /**
   * @return string
   */
  public function getIdProperty() {
    return $this->idProperty;
  }

  public static function initWithNewId($app) {
    $newModelObj = self::init($app);
    $newModelObj->{$newModelObj->idProperty} = $newModelObj->getNewId();
    return $newModelObj;
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

    // Gets the default ID column on the DataConnection side
    if (empty($newModelObj->idColumn)) {
      $newModelObj->idColumn = $newModelObj->app->getDataConnection($newModelObj->connection)->getDefaultIdColumn();
    }

    // Allows overrides of idProperty for the name of the ID on the MABI side
    if (empty($newModelObj->idProperty)) {
      //
      $rClass = new \ReflectionClass($newModelObj);
      $rProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
      foreach ($rProperties as $rProperty) {
        /*
         * Looks for the '@field id' directive to set as the id property
         */
        if (in_array('id', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field'))) {
          $newModelObj->idProperty = $rProperty->getName();
          break;
        }
      }
      if (empty($newModelObj->idProperty)) {
        $newModelObj->idProperty = 'id';
      }
    }

    $newModelObj->{$newModelObj->idProperty} = NULL;

    return $newModelObj;
  }

  public function getNewId() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    return $dataConnection->getNewId();
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
        $model->load($foundObject);
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
      $model->load($foundObject);
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
  protected function loadField($type, &$parameter, $result) {
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
            $model->load($result);
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

  public function loadFromExternalSource($source) {
    $this->load($source, TRUE);
  }

  /**
   * Loads the data for the model from a PHP array or a json string into the current model object using reflection
   * and MABI annotations.
   *
   * @param $resultArray array|string Either an associative array that maps to the model or a JSON string which can be turned into one
   * @param $sanitizeArray bool Whether to clean up $resultArray
   *
   * @throws \Exception
   */
  protected function load($resultArray, $sanitizeArray = FALSE) {
    if (!is_array($resultArray)) {
      $resultArray = json_decode($resultArray, TRUE);
      if (!is_array($resultArray)) {
        throw new \Exception("Invalid JSON used to load a model");
      }
    }

    $rClass = new \ReflectionClass($this);
    $rProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);

    if (!empty($resultArray[$this->idColumn])) {
      if (!$sanitizeArray) {
        $dataConnection = $this->app->getDataConnection($this->connection);
        $this->{$this->idProperty} = $dataConnection->convertFromNativeId($resultArray[$this->idColumn]);
      }
      unset($resultArray[$this->idColumn]);
      unset($resultArray[$this->idProperty]);
    }

    foreach ($rProperties as $rProperty) {
      if (!array_key_exists($rProperty->name, $resultArray)) {
        continue;
      }

      // Ignores setting any model property with 'internal' or 'system' options if sanitizing the input
      $fieldOptions = ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field');
      if ($sanitizeArray && (in_array('internal', $fieldOptions) || in_array('system', $fieldOptions))) {
        unset($resultArray[$rProperty->getName()]);
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
          if (!empty($resultArray[$rProperty->getName()])) {
            foreach ($resultArray[$rProperty->getName()] as $listResult) {
              $this->loadField($type, $parameter, $listResult);
              $outArr[] = $parameter;
            }
          }
          $this->{$rProperty->getName()} = $outArr;
        }
        else {
          $this->loadField($type, $this->{$rProperty->getName()}, $resultArray[$rProperty->getName()]);
        }
      }
      unset($resultArray[$rProperty->getName()]);
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
    $result = $dataConnection->findOneByField($this->idColumn, $dataConnection->convertToNativeId($id),
      $this->table, $this->readFields);
    if ($result == NULL) {
      return FALSE;
    }
    $this->load($result);
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
    if ($fieldName == $this->idColumn) {
      $value = $dataConnection->convertToNativeId($value);
    }
    $result = $dataConnection->findOneByField($fieldName, $value, $this->table, $this->readFields);
    if ($result == NULL) {
      return FALSE;
    }
    $this->load($result);
    return TRUE;
  }

  protected function getPropertyArrayValue($value, $forOutput = FALSE) {
    if (!is_object($value)) {
      return $value;
    }
    else {
      $propClass = new \ReflectionClass($value);

      if ($propClass->isSubclassOf('\MABI\Model')) {
        /**
         * @var $subModel \MABI\Model
         */
        $subModel = $value;
        return $subModel->getPropertyArray($forOutput);
      }
      elseif ($propClass->name == 'DateTime' || $propClass == '\DateTime') {
        /**
         * @var $date \DateTime
         */
        $date = $value;
        return $date->getTimestamp();
      }
    }

    return NULL;
  }

  public function getPropertyArray($forOutput = FALSE) {
    $rClass = new \ReflectionClass($this);

    $outArr = array();
    $rProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    foreach ($rProperties as $rProperty) {
      /*
       * Ignores writing any model property with 'external' option
       */
      if (!$forOutput && in_array('external', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field'))) {
        continue;
      }
      if ($forOutput && in_array('internal', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field'))) {
        continue;
      }
      if (in_array('system', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field'))) {
        continue;
      }

      if (is_array($this->{$rProperty->getName()})) {
        foreach ($this->{$rProperty->getName()} as $k => $v) {
          $outArr[$rProperty->getName()][$k] = $this->getPropertyArrayValue($v, $forOutput);
        }
      }
      else {
        $outArr[$rProperty->getName()] = $this->getPropertyArrayValue($this->{$rProperty->getName()}, $forOutput);
      }
    }
    if (!empty($this->{$this->idProperty})) {
      if (!$forOutput) {
        $dataConnection = $this->app->getDataConnection($this->connection);
        $outArr[$this->idColumn] = $dataConnection->convertToNativeId($this->{$this->idProperty});
      }
      else {
        $outArr[$this->idProperty] = $this->{$this->idProperty};
      }
    }
    if (!empty($this->_remainingReadResults) && !$forOutput) {
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
    $this->load($propArray);
  }

  /**
   * todo: docs
   */
  public function save() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $propArray = $this->getPropertyArray();
    $dataConnection->save($this->table, $propArray, $this->idColumn,
      $dataConnection->convertToNativeId($this->{$this->idProperty}));
    $this->load($propArray);
  }

  /**
   * todo: docs
   */
  public function delete() {
    $dataConnection = $this->app->getDataConnection($this->connection);
    $dataConnection->deleteByField($this->idColumn, $dataConnection->convertToNativeId($this->{$this->idProperty}),
      $this->table);
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

  public function getDocOutput(Parser $parser) {
    $fieldDocs = array();

    $rClass = new \ReflectionClass($this);
    $rProperties = $rClass->getProperties(\ReflectionProperty::IS_PUBLIC);
    foreach ($rProperties as $rProperty) {
      /*
       * Ignores writing any model property with 'internal' or 'system' options
       */
      if (in_array('internal', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field')) ||
        in_array('system', ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'field'))
      ) {
        continue;
      }

      $varType = 'unknown';
      // Pulls out the type following the pattern @var <TYPE> from the doc comments of the property
      $varDocs = ReflectionHelper::getDocDirective($rProperty->getDocComment(), 'var');
      if (!empty($varDocs)) {
        $varType = $varDocs[0];
      }

      $fieldDoc = array(
        'name' => $rProperty->getName(),
        'type' => $varType,
        'doc' => $parser->parse(ReflectionHelper::getDocText($rProperty->getDocComment()))
      );
      $fieldDocs[ /* $rProperty->getName() */] = $fieldDoc;
    }

    return array(
      'name' => get_called_class(),
      'fielddocs' => $fieldDocs,
// todo: Add 'SampleJSON' so that it can be copied into requests
    );
  }
}
