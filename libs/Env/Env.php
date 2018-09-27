<?php

namespace libs\Env;

class Env
{
    /**
     * Store ENV data
     */
    private static $env;

    /**
     * Read ENV from file and store in property
     */
    public static function setEnvFromFile($filePath)
    {
        $fileContents = file($filePath);
        self::$env = [];

        foreach ($fileContents as $line)
        {
            $line = trim($line);

            if (strlen($line) === 0 
                || strpos($line, '#') !== false)
            {
                continue;
            }

            $envArr = explode('=', $line);
            self::$env[$envArr[0]] = $envArr[1];
        }
    }

    /**
     * Get particular variable from ENV
     */
    public static function get($var)
    {
        return isset(self::$env[$var]) ? self::$env[$var] : null;
    }

    /**
     * Get all data from ENV
     */
    public static function getEnv()
    {
        return self::$env;
    }
}