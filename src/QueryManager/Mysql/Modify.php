<?php

namespace App\QueryManager\Mysql;

use App\QueryManager\Interfaces\IQueryManager;

class Modify implements IQueryManager
{
    private $table;

    private $conditions = [];

    private $columns = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function __toString(): string
    {
        $tas = 'UPDATE ' . $this->table
            . ' SET ' . implode(', ', $this->columns)
            . ($this->conditions === [] ? '' : ' WHERE ' . implode(' ', $this->conditions));
        return $tas;
    }

    public function where(string ...$where): self
    {
        foreach ($where as $arg) {
            $this->conditions[] = $arg;
        }
        return $this;
    }

    public function set(string ...$columns): self
    {
        foreach ($columns as $column) {
            $this->columns[] = "$column = :$column";
        }
        return $this;
    }
}