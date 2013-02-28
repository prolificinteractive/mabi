<?php

namespace MABI;

abstract class DataConnection {
  abstract function getDefaultIdColumn();

  abstract function insert($table, $data);

  abstract function findAll($table);

  abstract function findOneByField($field, $value, $table);

  abstract function clearAll($table);

  abstract function query($table, $query);
}
