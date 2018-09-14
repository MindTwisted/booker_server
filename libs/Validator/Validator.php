<?php

namespace libs\Validator;

use libs\QueryBuilder\src\QueryBuilder;
use libs\Input\Input;

class Validator
{
    /**
     * Validation errors
     */
    private $errors;

    /**
     * QueryBuilder instance
     */
    private static $builder;

    /**
     * Database tables prefix for QueryBuilder
     */
    private static $dbPrefix = '';
    
    /**
     * Validation rules
     */
    private static $rules = [
        "/^required$/" => [
            'method' => 'checkRequired',
            'message' => 'requiredMessage',
        ],
        "/^numeric$/" => [
            'method' => 'checkNumeric',
            'message' => 'numericMessage',
        ],
        "/^integer$/" => [
            'method' => 'checkInteger',
            'message' => 'integerMessage',
        ],
        "/^min:([0-9]+)$/" => [
            'method' => 'checkMin',
            'message' => 'minMessage',
        ],
        "/^max:([0-9]+)$/" => [
            'method' => 'checkMax',
            'message' => 'maxMessage',
        ],
        "/^minLength:([0-9]+)$/" => [
            'method' => 'checkMinLength',
            'message' => 'minLengthMessage',
        ],
        "/^email$/" => [
            'method' => 'checkEmail',
            'message' => 'emailMessage',
        ],
        "/^unique:([a-zA-Z0-9\-\_]+):([a-zA-Z0-9\-\_]+):*([a-zA-Z0-9\-\_]*)$/" => [
            'method' => 'checkUnique',
            'message' => 'uniqueMessage',
        ],
        "/^exists:([a-zA-Z0-9\-\_]+):([a-zA-Z0-9\-\_]+)$/" => [
            'method' => 'checkExists',
            'message' => 'existsMessage',
        ],
        "/^included:\(([a-zA-Z0-9\-\_\,\s]+)\)$/" => [
            'method' => 'checkIncluded',
            'message' => 'includedMessage',
        ],
        "/^alpha_dash$/" => [
            'method' => 'checkAlphaDash',
            'message' => 'alphaDashMessage',
        ],
    ];

    /**
     * Generate message for required rule failure
     */
    private static function requiredMessage($field)
    {
        return "$field field is required.";
    }

    /**
     * Generate message for numeric rule failure
     */
    private static function numericMessage($field)
    {
        return "$field field requires numeric value.";
    }

    /**
     * Generate message for integer rule failure
     */
    private static function integerMessage($field)
    {
        return "$field field requires integer value.";
    }

    /**
     * Generate message for min rule failure
     */
    private static function minMessage($field, $min)
    {
        return "$field field requires numeric value greater or equal than $min.";
    }

    /**
     * Generate message for max rule failure
     */
    private static function maxMessage($field, $max)
    {
        return "$field field requires numeric value less or equal than $max.";
    }

    /**
     * Generate message for minLength rule failure
     */
    private static function minLengthMessage($field, $length)
    {
        return "$field field requires string longer than $length characters.";
    }

    /**
     * Generate message for email rule failure
     */
    private static function emailMessage($field)
    {
        return "$field field must be a valid email address.";
    }

    /**
     * Generate message for unique rule failure
     */
    private static function uniqueMessage($field)
    {
        return "$field field value is already exists in database.";
    }

    /**
     * Generate message for exists rule failure
     */
    private static function existsMessage($field)
    {
        return "$field field value doesn't exists in database.";
    }

    /**
     * Generate message for included rule failure
     */
    private static function includedMessage($field, $list)
    {
        return "$field field value doesn't included in available list of values: $list.";
    }

    /**
     * Generate message for alphaDash rule failure
     */
    private static function alphaDashMessage($field)
    {
        return "$field field requires only alphanumeric characters with dashes, underscores and spaces.";
    }

    /**
     * Required rule check
     */
    private static function checkRequired($field)
    {
        if (empty($field) && $field !== '0' && strlen($field) === 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Numeric rule check
     */
    private static function checkNumeric($field)
    {
        if ($field) 
        {
            if (is_array($field)) 
            {
                $isNumeric = true;

                foreach ($field as $row) 
                {
                    if (!is_numeric($row)) 
                    {
                        $isNumeric = false;
                    }

                }

                return $isNumeric;
            }

            return is_numeric($field);
        }

        return true;
    }

    /**
     * Integer rule check
     */
    private static function checkInteger($field)
    {
        if ($field) 
        {
            if (is_array($field)) 
            {
                $isInteger = true;

                foreach ($field as $row) 
                {
                    if (!ctype_digit(strval($row))) 
                    {
                        $isInteger = false;
                    }

                }

                return $isInteger;
            }

            return ctype_digit(strval($field));
        }

        return true;
    }

    /**
     * Email rule check
     */
    private static function checkEmail($field)
    {
        if (empty($field) && $field !== '0')
        {
            return true;
        }

        return !!filter_var($field, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Min rule check
     */
    private static function checkMin($field, $min)
    {
        if (empty($field) && $field !== '0')
        {
            return true;
        }

        if (is_array($field)) 
        {
            $isValid = true;

            foreach ($field as $row) 
            {
                if (!is_numeric($row) || !(+$row >= +$min)) 
                {
                    $isValid = false;
                }

            }

            return $isValid;
        }

        return is_numeric($field) && +$field >= +$min;
    }

    /**
     * Max rule check
     */
    private static function checkMax($field, $max)
    {
        if (empty($field) && $field !== '0')
        {
            return true;
        }

        if (is_array($field)) 
        {
            $isValid = true;

            foreach ($field as $row) 
            {
                if (!is_numeric($row) || !(+$row <= +$max)) 
                {
                    $isValid = false;
                }

            }

            return $isValid;
        }

        return is_numeric($field) && +$field <= +$max;
    }

    /**
     * MinLength rule check
     */
    private static function checkMinLength($field, $minLength)
    {
        return $field && strlen($field) >= $minLength;
    }

    /**
     * Unique rule check
     */
    private static function checkUnique($field, $uTable, $uField, $exceptId = 0)
    {
        if (empty($field) && $field !== '0')
        {
            return true;
        }

        $uTable = self::$dbPrefix . $uTable;

        $result = self::$builder->table($uTable)
                                ->fields(['*'])
                                ->where([$uField, '=', $field])
                                ->limit(1)
                                ->select()
                                ->run();

        if (count($result) !== 0)
        {
            return +$result[0]['id'] === +$exceptId;
        }

        return true;
    }

    /**
     * Exists rule check
     */
    private static function checkExists($field, $uTable, $uField)
    {
        if (empty($field) && $field !== '0')
        {
            return true;
        }
        
        $uTable = self::$dbPrefix . $uTable;
 
        if (!is_array($field))
        {
            $result = self::$builder->table($uTable)
                                    ->fields([$uField])
                                    ->where([$uField, '=', $field])
                                    ->limit(1)
                                    ->select()
                                    ->run();

            return count($result) > 0;
        }

        $sqlQuery = "SELECT $uField FROM $uTable WHERE";

        foreach($field as $row)
        {
            $sqlQuery .= " $uField = ? OR";
        }

        $sqlQuery = trim($sqlQuery, 'OR');

        $result = self::$builder->raw($sqlQuery, $field)->fetchAll(\PDO::FETCH_ASSOC);

        return count($result) === count($field);
    }

    /**
     * Included rule check
     */
    private static function checkIncluded($field, $list)
    {
        if ($field)
        {
            $isIncluded = false;
            $list = explode(',', $list);

            foreach ($list as $item)
            {
                if ($field === trim($item))
                {
                    $isIncluded = true;
                }
            }

            return $isIncluded;
        }

        return false;
    }

    /**
     * AlphaDash rule check
     */
    private static function checkAlphaDash($field)
    {
        if (empty($field) && $field !== '0')
        {
            return true;
        }

        return !!preg_match('/^[\w\s\-]+$/', $field);
    }

    /**
     * Constructor
     */
    public function __construct($errors)
    {
        $this->errors = $errors;
    }

    /**
     * Check if validation fails
     */
    public function fails()
    {
        return count($this->errors) > 0;
    }

    /**
     * Get validation errors
     */
    public function errors()
    {
        return $this->errors;
    }

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

    /**
     * Make validation
     */
    public static function make(...$args)
    {
        if (count($args) > 1)
        {
            if (!is_array($args[0]) 
                || !is_array($args[1]))
            {
                throw new \Exception("Validator 'make' method requires array type arguments.");
            }

            if (array_keys($args[0]) !== array_keys($args[1]))
            {
                throw new \Exception("Validator 'make' method requires variables array and rules array to have equal keys.");
            }
        }

        $array = count($args) === 1 ? $args[0] : $args[1];
        $errors = [];

        foreach ($array as $key => $val) 
        {
            $validateRules = explode('|', $val);
            $messages = [];

            foreach (self::$rules as $rKey => $rVal) 
            {
                foreach ($validateRules as $vrKey => $vrVal) 
                {
                    $matchResult = preg_match($rKey, $vrVal, $matches);

                    $first = isset($matches[1]) ? $matches[1] : null;
                    $second = isset($matches[2]) ? $matches[2] : null;
                    $third = isset($matches[3]) ? $matches[3] : null;

                    if ($matchResult) 
                    {
                        $methodName = $rVal['method'];
                        $message = $rVal['message'];

                        $fieldValue = count($args) === 1 ? Input::get($key) : $args[0][$key];

                        if (!self::$methodName($fieldValue, $first, $second, $third)) 
                        {
                            $messages[] = self::$message(ucfirst($key), $first);
                        }
                    }
                }
            }

            if (count($messages)) 
            {
                $errors[$key] = $messages;
            }
        }

        return new self($errors);
    }
}
