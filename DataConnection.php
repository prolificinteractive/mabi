<?php

namespace MABI;

interface DataConnection {
  function getNewId();

  function convertToNativeId($stringId);

  function convertFromNativeId($nativeId);

  function getDefaultIdColumn();

  function insert($table, $data);

  function save($table, $data, $field, $value);

  function findAll($table);

  function findOneByField($field, $value, $table, array $fields = array());

  function findAllByField($field, $value, $table, array $fields = array());

  function deleteByField($field, $value, $table);

  function clearAll($table);

  function query($table, $query);
}
