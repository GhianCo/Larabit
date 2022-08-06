<?php


namespace App\Utils;

use DateTime;

abstract class DatetimeUtils
{

    public static function getToday(){
        return date("Y-m-d H:i:s");
    }

    public static function addTime($date,$value, $format = "Y-m-d H:i:s"){
        return date($format,strtotime($value, strtotime($date)));
    }

    public static function diffMinutes($datestart, $dateend){

        try {
            $dateStart = new DateTime($datestart);
            $dateEnd = new DateTime($dateend);
            $diff = $dateStart->diff($dateEnd);

            return ($diff->days * 24 * 60) + ($diff->h * 60) + ($diff->i);

        } catch (\Exception $e) {
            return 0;
        }

    }

    public static function getDateFormat($fecha, $formato = "d/m/Y H:i:s")
    {
        try {
            if($fecha == null || $fecha ==""){
                return "";
            }
            $fecha = new DateTime($fecha);
            return $fecha->format($formato);
        } catch (\Exception $er) {
            return "";
        }
    }

}