<?php

namespace libs\Input;

class Input
{
    /**
     * Input
     */
    private static $input = [];

    /**
     * Collect input from GET, POST, PUT, DELETE requests
     */
    public static function collectInput()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ?
            $_SERVER['REQUEST_METHOD'] : null;

        foreach ($_GET as $key => $value)
        {
            self::$input[$key] = $value;
        }

        if ('POST' === $method)
        {
            foreach ($_POST as $key => $value)
            {
                self::$input[$key] = $value;
            }
        }
       
        if ('PUT' === $method) {
            parse_str(file_get_contents('php://input'), $_PUT);

            foreach ($_PUT as $key => $value)
            {
                self::$input[$key] = $value;
            }
        }

        if ('DELETE' === $method) {
            parse_str(file_get_contents('php://input'), $_DELETE);

            foreach ($_DELETE as $key => $value)
            {
                self::$input[$key] = $value;
            }
        }
    }

    /**
     * Get value from collected input
     */
    public static function get($field)
    {
        return isset(self::$input[$field]) ? self::$input[$field] : null;
    }

    /**
     * Get all collected input
     */
    public static function all()
    {
        return self::$input;
    }

    /**
     * Get only selected fields from collected input
     */
    public static function only(array $fields)
    {
        return array_filter(self::$input, function($input) use ($fields) {
            return in_array($input, $fields);
        }, ARRAY_FILTER_USE_KEY);
    }
}