<?php

namespace Phormium\Helper;

/**
 * Provides various assertion methods.
 */
class Assert
{
    /**
     * Checks whether the given value is an integer or a string containing
     * an integer, positive or negative.
     */
    public static function isInteger($value)
    {
        return is_int($value) || (
            is_string($value) && strlen($value) > 0 && (
                ctype_digit($value) || (
                    $value[0] == '-' &&
                    ctype_digit(substr($value, 1))
                )
            )
        );
    }

    /**
     * Checks whether the given value is a positive integer or a string
     * containing one.
     */
    public static function isPositiveInteger($value)
    {
        return (is_int($value) && $value >= 0) ||
            (is_string($value) && ctype_digit($value));
    }
}
