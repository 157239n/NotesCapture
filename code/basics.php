<?php

/**
 * These are functions that are so god damn simple that they don't warrant any OOP mechanisms and shall be loaded directly.
 */

namespace Kelvinho\Notes {

    use Exception;

    /**
     * Map.
     *
     * @param array $list Initial list
     * @param callable $function Mapping function. The element, the index (or key), and extra data will be given to this function
     * @param null $data Extra data. Can be left out
     * @return array Mapped list
     */
    function map(array $list, callable $function, $data = null): array {
        $newList = [];
        foreach ($list as $key => $value) {
            $newList[$key] = $function($value, $key, $data);
        }
        return $newList;
    }

    /**
     * Filter. If the predicate returns true then the element makes it.
     *
     * @param array $list Initial list
     * @param callable $predicate Predicate function. The element, the key/index, and extra data will be given to this function
     * @param null $data Extra data. Can be left out
     * @param bool $ordered Whether to preserve old keys or throw them away. True if throw them away
     * @return array
     */
    function filter(array $list, callable $predicate, $data = null, bool $ordered = true): array {
        $newList = [];
        foreach ($list as $key => $value) {
            if ($predicate($value, $key, $data)) {
                if ($ordered) {
                    $newList[] = $value;
                } else {
                    $newList[$key] = $value;
                }
            }
        }
        return $newList;
    }

    /**
     * Gets the title of a website. Returns an empty string if nothing is found.
     *
     * @param string $url
     * @return string
     */
    function getTitle(string $url): string {
        try {
            $str = file_get_contents($url, false, stream_context_create(array('http' => array('timeout' => 1))));
            if ($str === false) return "";
            if (strlen($str) > 0) {
                $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
                /** @noinspection RegExpRedundantEscape */
                preg_match("/\<title\>(.*)\<\/title\>/i", $str, $title); // ignore case
                return $title[1] ?? "";
            }
            return "";
        } catch (Exception $e) {
            return "";
        }
    }

    /**
     * For debugging, used in conjunction with Logs::log()
     *
     * @param $variable
     * @return string
     */
    function dump($variable): string {
        ob_start();
        var_dump($variable);
        return ob_get_clean();
    }
}
