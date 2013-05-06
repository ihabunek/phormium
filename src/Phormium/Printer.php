<?php

namespace Phormium;

/**
 * Helper class for printing QuerySet data to the console.
 */
class Printer
{
    /** Number of spaces to leave between columns. */
    const COLUMN_PADDING = 3;

    /** Maximum number of chars in a column. Longer values will be trucnated. */
    const COLUMN_MAX_LENGTH = 50;

    /**
     * Takes a QuerySet, fetches the data which matches the QuerySet and
     * prints it to the console in a human readable way.
     *
     * If using non-ascii characters, make sure to set mb_internal_encoding
     * to the appropreiate value, e.g.:
     * <pre>mb_internal_encoding('UTF-8');</pre>
     *
     * @param array $array Input array.
     * @param array $return If set to true, the dump will be returned as string
     * 		instead of printing it.
     */
    public static function dump(QuerySet $querySet, $return = false)
    {
        $data = $querySet->fetch();

        if (empty($data)) {
            return;
        }

        $meta = $querySet->getMeta();
        $columns = $meta->columns;

        // Record column names lengths
        $lengths = array();
        foreach ($columns as $name) {
            $lengths[$name] = mb_strlen($name);
        }

        // Process data for display and record data lengths
        foreach($data as $model) {

            if (!($model instanceof Model)) {
                throw new \Exception("Invalid input for dump().");
            }

            foreach($model as $key => $value) {
                $model->$key = self::prepareValue($value);

                if (mb_strlen($value) > $lengths[$key]) {
                    $lengths[$key] = mb_strlen($value);
                }
            }
        }

        // Determine total row length
        $totalLength = 0;
        foreach ($lengths as $len) {
            $totalLength += $len;
        }

        // Account for padding between columns
        $totalLength += self::COLUMN_PADDING * (count($columns) - 1);

        // Start outputting data
        $output = "";

        // Print the titles
        foreach($columns as $column) {
            $output .= self::strpad($column, $lengths[$column]);
            $output .= str_repeat(" ", self::COLUMN_PADDING);
        }
        $output .= PHP_EOL;

        // Print the line under titles
        $output .= str_repeat("=", $totalLength) . PHP_EOL;

        // Print the rows
        foreach ($data as $model) {
            foreach ($model as $column => $value) {
                $output .= self::strpad($value, $lengths[$column]);
                $output .= str_repeat(" ", self::COLUMN_PADDING);
            }
            $output .= PHP_EOL;
        }

        if ($return) {
            return $output;
        }

        echo $output;
    }

    /**
     * Replacement for strpad() which uses mb_* functions.
     */
    private static function strpad($value, $length)
    {
        $buf = "";
        $padLength = $length - mb_strlen($value);

        // Sanity check: $padLength can be sub-zero when incorrect
        // mb_internal_encoding is used.
        if ($padLength > 0) {
            return str_repeat(" ", $padLength) . $value;
        } else {
            return $value;
        }
    }

    /**
     * Makes sure the value is a string and trims it to MAX_LENGTH chars if
     * needed.
     */
    private static function prepareValue($value)
    {
        // Silence operator to prevent notices like Array to string conversion
        $value = @trim(strval($value));

        // Trim to max allowed length
        if (mb_strlen($value) > self::COLUMN_MAX_LENGTH)
        {
            $value = mb_substr($value, 0, self::COLUMN_MAX_LENGTH - 3) . '...';
        }

        return $value;
    }
}