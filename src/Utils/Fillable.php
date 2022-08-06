<?php

namespace App\Utils;

trait Fillable
{
    private function fill($data)
    {
        foreach ($this->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $this->attributes[$field] = $data[$field];
            }
        }
    }
}
