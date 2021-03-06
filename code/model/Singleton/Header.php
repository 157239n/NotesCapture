<?php

namespace Kelvinho\Notes\Singleton;

/**
 * Class Header. Convenience class for setting http response code and exiting.
 *
 * @package Kelvinho\Notes\Singleton
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class Header {
    public static function ok() {
        http_response_code(200);
        exit(0);
    }

    public static function forbidden() {
        http_response_code(403);
        exit(1);
    }

    public static function badRequest() {
        http_response_code(400);
        exit(1);
    }

    public static function notFound() {
        http_response_code(404);
        exit(1);
    }

    public static function redirectToHome() {
        self::redirect("");
    }

    public static function redirectBare(string $location) {
        header("Location: $location");
        http_response_code(302);
        exit(0);
    }

    public static function redirect(string $location) {
        self::redirectBare(CHARACTERISTIC_DOMAIN . "/$location");
    }
}