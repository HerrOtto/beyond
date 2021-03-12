<?php

/*
 * Database base connection class used by database classes
 */

abstract class beyondDatabaseDriver
{

    protected $connection;

    // Kinds
    protected $textNames = array('longtext', 'longstring', 'text');
    protected $stringNames = array('string', 'varchar');
    protected $integerNames = array('number', 'int', 'integer');
    protected $decimalNames = array('decimal', 'float', 'double', 'real', 'single');

    abstract public function escape($string);
    abstract public function internalFetch($queryResult);
    abstract public function query($sql);
    abstract public function insertId();
    abstract public function select($tableName, $fieldArray, $whereArray, $offset = false, $limit = false);
    abstract public function count($tableName, $whereArray);
    abstract public function insert($tableName, $fieldArray);
    abstract public function update($tableName, $fieldArray, $whereArray);
    abstract public function delete($tableName, $whereArray);
    abstract public function tableExists($tableName);
    abstract public function tableCreate($tableName, $fieldListArray);
    abstract public function tableColumnAdd($tableName, $fieldName, $field);
    abstract public function tableColumnDrop($tableName, $fieldName);
    abstract public function tableInfo($tableName);
    abstract public function tableDrop($tableName);
    abstract public function tableList();

}

class dbQueryClass
{

    protected $sql;
    protected $result;
    protected $parent;

    function __construct($sql, $result, &$parent)
    {
        $this->sql = $sql;
        $this->result = $result;
        $this->parent = $parent;
    }

    public function fetch() {
        return $this->parent->internalFetch($this->result);
    }

}
