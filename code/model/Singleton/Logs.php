<?php /** @noinspection PhpUnused */

namespace Kelvinho\Notes\Singleton;

use mysqli;
use RuntimeException;

/**
 * Class Logs, handles logging.
 *
 * @package Kelvinho\Notes\Singleton
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class Logs {
    /**
     * Logs an unreachable place, needs further debugging.
     *
     * @param string $where The general place where this is called, to locate the problem easier
     */
    public static function unreachableState(string $where): void {
        Logs::error("This is supposed to be unreachable. Where: $where");
    }

    /**
     * Logs with "Error: " in front and exits the script.
     *
     * @param string $message The message to log
     */
    public static function error(string $message): void {
        throw new RuntimeException("Error: " . $message);
    }

    /**
     * Logs stuff.
     *
     * @param string $message The message to log
     */
    public static function log(string $message): void {
        file_put_contents(LOG_FILE, $message . "\n", FILE_APPEND);
    }

    public static function mysql(mysqli $mysqli): void {
        Logs::error("Mysql failed. Error: $mysqli->error");
    }
}
