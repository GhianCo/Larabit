<?php

namespace App\QueryManager\Mysql;

use App\QueryManager\Interfaces\IQueryManager;

class Insert implements IQueryManager
{
    private $table;

    private $columns = [];

    private $values = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function __toString(): string
    {
        return 'INSERT INTO ' . $this->table
            . ' (' . implode(', ', $this->columns) . ') VALUES (' . implode(', ', $this->values) . ')';
    }

    public function columns(string ...$columns): self
    {
        foreach ($columns as $column) {
            $this->columns[] = $column;
            $this->values[] = ":$column";
        }
        return $this;
    }
}