<?php

namespace Phormium\Config;

/**
 * Loads and decodes JSON configuration files.
 */
class JsonConfigLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        $data = $this->loadFile($resource);

        $config = json_decode($data, true);

        $errorCode = json_last_error();
        if ($errorCode !== JSON_ERROR_NONE) {
            $msg = $this->jsonLastErrorMessage();
            throw new \Exception("Failed parsing JSON configuration: $msg");
        }

        return $config;
    }

    private function jsonLastErrorMessage()
    {
        if (function_exists('json_last_error_msg')) {
            // Introduced in PHP 5.5
            $msg = json_last_error_msg();
        } else {
            // Unreachanble code for PHP >= 5.5
            // @codeCoverageIgnoreStart
            $msg = "Error code \"$errorCode\".";
            // @codeCoverageIgnoreEnd
        }

        return $msg;
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) &&
            pathinfo($resource, PATHINFO_EXTENSION) === 'json';
    }
}
