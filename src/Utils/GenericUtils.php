<?php


namespace App\Utils;

abstract class GenericUtils
{

    public static function jsonToObject($input)
    {
        return json_decode((string)json_encode($input), false);
    }

    public static function jsonDecode($input)
    {
        return json_decode($input, false);
    }

    public static function objectToArray($object)
    {
        return (array)$object;
    }

    public static function arrayValuesToString($array, $separation = ",")
    {

        return implode($separation, $array);

    }

    public static function arrayValuesToStringKey($array_values, $key, $separator = ", ")
    {
        $stringReturn = null;
        $array_valueString = array();

        foreach ($array_values as $value) {
            if (isset($value->$key)) {
                $array_valueString[] = $value->$key;
            }
        }

        $stringReturn = implode($separator, $array_valueString);

        return $stringReturn;
    }

    public static function getPorcentaje($valor, $total)
    {

        if ($total > 0) {
            return round(($valor / $total) * 100, 2);
        } else {
            return 0;
        }

    }

    public static function minutesToHour($value)
    {
        if (is_numeric($value)) {
            return (round($value * 1 / 60, 2));
        }

        return $value;
    }

    public static function likeArray(array $arr, string $patron, $field = null)
    {
        $arrayToReturn = array();
        $arrayFilter = array_filter($arr, function ($item) use ($patron, $field) {
            if (stripos($item->$field, $patron) !== false) {
                return true;
            }
            return false;
        });

        foreach ($arrayFilter as $dataFilter) {
            $arrayToReturn[] = $dataFilter;
        }

        return $arrayToReturn;
    }

}