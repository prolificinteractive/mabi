<?php

namespace MABI;

abstract class DataConnection {
  abstract function insert($table, $data);
  abstract function findAll($table);
  abstract function clearAll($table);
}
