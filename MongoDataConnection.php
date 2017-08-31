<?php

namespace MABI;

include_once __DIR__ . '/DataConnection.php';

/**
 * todo: docs
 */
class MongoDataConnection implements DataConnection {

  /**
   * @var \MongoDB
   */
  protected $db = NULL;

  /**
   * @return \MongoDB
   */
  public function getDb() {
    return $this->db;
  }

  /**
   * todo: docs
   *
   * @param $connectionURI string
   *
   * @return MongoDataConnection
   */
  public static function create($connectionURI) {
    $connection = new MongoDataConnection();
    $database = parse_url($connectionURI, PHP_URL_PATH);
    $database = ltrim($database, '/');
    $mongo = new \MongoClient($connectionURI);
    $connection->db = $mongo->selectDB($database);

    return $connection;
  }

  public function getDefaultIdColumn() {
    return '_id';
  }

  function getNewId() {
    $newId = new \MongoId();
    return $newId->__toString();
  }

  function convertToNativeId($stringId) {
    return new \MongoId($stringId);
  }

  function convertFromNativeId($nativeId) {
    if (is_object($nativeId) && get_class($nativeId) == 'MongoId') {
      return $nativeId->__toString();
    }
    return $nativeId;
  }

  public function findAll($table) {
    $return = $this->db->selectCollection($table)->find();

    $mongodata = array();
    while ($return->hasNext()) {
      $return->getNext();
      $mongodata[] = $return->current();
    }

    return $mongodata;
  }

  public function insert($table, $data) {
    $this->db->selectCollection($table)->insert($data);
    return $data;
  }

  function save($table, $data, $field, $value) {
    $this->db->selectCollection($table)->update(array($field => $value), $data);
  }

  public function clearAll($table) {
    $this->db->selectCollection($table)->drop();
  }

  public function findOneByField($field, $value, $table, array $fields = array()) {
    $result = $this->db->selectCollection($table)->findOne(array($field => $value), $fields);
    if (empty($result)) {
      return NULL;
    }

    return $result;
  }

  function findAllByField($field, $value, $table, array $fields = array()) {
    $return = $this->db->selectCollection($table)->find(array($field => $value), $fields);

    $mongodata = array();
    while ($return->hasNext()) {
      $return->getNext();
      $mongodata[] = $return->current();
    }

    return $mongodata;
  }

  function query($table, $query) {
    if (isset($query['group'])) {
      if (empty($query['group']['condition'])) {
        $return = $this->db->selectCollection($table)->group(
          $query['group']['_id'],
          $query['group']['initial'],
          $query['group']['reduce']);
      } else {
        $return = $this->db->selectCollection($table)->group(
          $query['group']['_id'],
          $query['group']['initial'],
          $query['group']['reduce'],
          $query['group']['condition']);
      }
      return $return;
    }

    $mquery = $query['query'];
    $return = $this->db->selectCollection($table)->find($mquery);
    if (!empty($query['skip'])) {
      $return = $return->skip($query['skip']);
    }
    if (!empty($query['limit'])) {
      $return = $return->limit($query['limit']);
    }
    if (!empty($query['sort'])) {
      $return = $return->sort($query['sort']);
    }

    $mongodata = array();
    while ($return->hasNext()) {
      $return->getNext();
      $mongodata[] = $return->current();
    }

    return $mongodata;
  }

  /**
   * todo: docs
   *
   * @param $field
   * @param $value
   * @param $table
   */
  function deleteByField($field, $value, $table) {
    $this->db->selectCollection($table)->remove(array($field => $value));
  }

  /**
   *
   * @return int model count
   */
  public function count($table) {
    $collection = $this->db->selectCollection($table);
    $count      = $collection->find()->count();
    return $count;
  }

  /**
   * @param array $query Array with find parameters
   * @return int model count
   */
  public function countWithQuery($table, $query) {
    $collection = $this->db->selectCollection($table);
    $count      = $collection->find($query)->count();
    return $count;
  }
}
