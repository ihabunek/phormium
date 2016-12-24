<?php

namespace Phormium\Helper;

/**
 * Encodes and decodes JSON.
 */
class Json
{
    /**
     * Encodes the given data to JSON, throws an exception on error.
     *
     * @see json_encode()
     */
    public static function dump($data, $options = 0)
    {
        $json = json_encode($data, $options);

        if ($json === false) {
            $msg = json_last_error_msg();
            throw new \Exception("Failed dumping JSON: $msg");
        }

        return $json;
    }

    /**
     * Parses given JSON data to an object or array.
     *
     * @see json_decode()
     */
    public static function parse($json, $assoc = true)
    {
        $data = json_decode($json, $assoc);

        $errorCode = json_last_error();
        if ($errorCode !== JSON_ERROR_NONE) {
            $msg = json_last_error_msg();
            throw new \Exception("Failed parsing JSON: $msg");
        }

        return $data;
    }
}
