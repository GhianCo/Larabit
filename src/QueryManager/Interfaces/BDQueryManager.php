<?php

namespace App\QueryManager\Interfaces;

use App\QueryManager\Mysql\Insert;
use App\QueryManager\Mysql\Modify;
use App\QueryManager\Mysql\Select;

interface BDQueryManager
{
    public static function select(string ...$select): Select;

    public static function insert(string $into): Insert;

    public static function modify($table): Modify;

    public static function setTable($from);

    public static function getTable(): string;
}