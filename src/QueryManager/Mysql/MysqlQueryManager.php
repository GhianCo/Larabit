<?php

namespace App\QueryManager\Mysql;

use App\QueryManager\Interfaces\BDQueryManager;

class MysqlQueryManager extends \PDO implements BDQueryManager
{

    public static $table;

    public static function select(string ...$select): Select
    {
        return new Select($select, self::$table);
    }

    public static function insert(string $into): Insert
    {
        return new Insert($into);
    }

    public static function modify($table): Modify
    {
        return new Modify($table);
    }

    public static function setTable($table)
    {
        self::$table = $table;
    }

    public static function getTable(): string
    {
        return self::$table;
    }

}