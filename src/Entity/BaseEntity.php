<?php

namespace App\Entity;

use App\Utils\Fillable;

abstract class BaseEntity
{
    use Fillable;

    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $attributes = [];

    public function __construct(array $data = null)
    {
        if (isset($data)) {
            $this->fill($data);
        }
    }

    public function getKeyName()
    {
        return $this->primaryKey;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getFillable()
    {
        return $this->fillable;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($key)
    {
        if (!$this->keyHasValid($key)) {
            return false;
        }
        return $this->attributes[$key];
    }

    public function setAttribute($key, $value)
    {
        if (!$this->keyHasValid($key)) {
            return false;
        }
        $this->attributes[$key] = $value;
        return $this;
    }

    public function keyHasValid($key)
    {
        if (!$key || !in_array($key, $this->fillable)) {
            return false;
        }
        return true;
    }
}

?>