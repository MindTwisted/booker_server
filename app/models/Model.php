<?php

namespace app\models;

use libs\QueryBuilder\src\QueryBuilder;

class Model
{
    /**
     * QueryBuilder database prefix
     */
    protected static $dbPrefix = '';

    /**
     * QueryBuilder instance
     */
    protected static $builder;

    /**
     * Set database prefix
     */
    public static function setDbPrefix($prefix)
    {
        self::$dbPrefix = $prefix;
    }

    /**
     * Set QueryBuilder instance
     */
    public static function setBuilder(QueryBuilder $builder)
    {
        self::$builder = $builder;
    }
}