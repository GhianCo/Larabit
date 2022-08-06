<?php

namespace App\QueryManager\Mysql;

use App\QueryManager\Interfaces\IQueryManager;

class Select implements IQueryManager
{

    private $withCallFoundRows = false;

    private $fields = [];

    private $conditions = [];

    private $order = [];

    private $table = [];

    private $innerJoin = [];

    private $limit = '';

    public function __construct(array $fields, string $table = null)
    {
        $this->fields = $fields;
        $this->table = $table;
    }

    public function select(string ...$select): self
    {
        foreach ($select as $arg) {
            $this->fields[] = $arg;
        }
        return $this;
    }

    public function __toString(): string
    {
        return 'SELECT ' . ($this->withCallFoundRows ? ' SQL_CALC_FOUND_ROWS ' : ' ') . implode(', ', $this->fields)
            . ' FROM ' . $this->table
            . ($this->innerJoin === [] ? '' : ' INNER JOIN ' . implode(' INNER JOIN ', $this->innerJoin))
            . ($this->conditions === [] ? '' : ' WHERE ' . implode(' ', $this->conditions))
            . ($this->order === [] ? '' : ' ORDER BY ' . implode(', ', $this->order))
            . ($this->limit == '' ? '' : $this->limit);
    }

    public function callFoundRows(bool $withCallFoundRows): self
    {
        $this->withCallFoundRows = $withCallFoundRows;
        return $this;
    }

    public function where($conditions): self
    {
        $this->conditions = $conditions;
        return $this;
    }

    public function whereColumn($field, $operator = '=', $contional = ''): self
    {
        $this->conditions[] = sprintf(' %s %s :%s %s', $field, $operator, $field, $contional);
        return $this;
    }

    public function first(): self
    {
        $this->limit = ' limit 1 ';
        return $this;
    }

    public function page(int $page): self
    {
        $this->limit .= ' limit ' . $page;
        return $this;
    }

    public function perPage(int $perPage): self
    {
        $this->limit .= ', ' . $perPage;
        return $this;
    }

    public function orderBy(string ...$order): self
    {
        foreach ($order as $arg) {
            $this->order[] = $arg;
        }
        return $this;
    }

    public function innerJoin(string ...$innerJoin): self
    {
        foreach ($innerJoin as $arg) {
            $this->innerJoin[] = $arg;
        }
        return $this;
    }

}