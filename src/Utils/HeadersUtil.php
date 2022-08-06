<?php

namespace App\Utils;

use Firebase\JWT\JWT;
use App\Exception\Auth as AuthException;

abstract class HeadersUtil
{
    public static function getUserId() {
        $tokenDecoded = self::checkAuthToken();
        if ($tokenDecoded) {
            return $tokenDecoded->sub;
        }
        return null;
    }

    public static function checkAuthToken() {
        $headers = getallheaders();
        $jwtHeader = isset($headers['Authorization']) ? $headers['Authorization'] : false;
        $jwt_GET = isset($_GET["token"]) ? $_GET["token"] : false;
        if (!$jwtHeader && !$jwt_GET) {
            return null;
        }
        if (!$jwtHeader && $jwt_GET) {
            $jwtHeader = 'Bearer ' . $jwt_GET;
        }
        $jwt = explode('Bearer ', $jwtHeader);
        if (!isset($jwt[1])) {
            return null;
        }
        try {
            return JWT::decode($jwt[1], $_SERVER['SECRET_KEY'], ['HS256']);
        } catch (\UnexpectedValueException $e) {
            throw new AuthException('Acceso restringido: no tienes permisos para ver este recurso.', 403);
        }
    }
}
