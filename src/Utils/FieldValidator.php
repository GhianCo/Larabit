<?php

namespace App\Utils;


trait FieldValidator
{

    private function validator($inputData)
    {
        $fieldsInvalids = array();
        foreach ($this->fieldsRequired as $field) {

            if (!array_key_exists($field, $inputData)) {
                $fieldsInvalids[] = $field;
            }
        }
        return $fieldsInvalids;
    }
}