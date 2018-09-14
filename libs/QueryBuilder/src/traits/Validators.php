<?php

namespace libs\QueryBuilder\src\traits;

trait Validators
{
    /**
     * Check if array is sequential (with numeric indexes)
     *
     * @param array $array
     *
     * @return bool
     */
    private function checkSequentialArray(array $array)
    {
        return count(
                   array_filter(
                       array_keys($array),
                       'is_string'
                   )
               ) == 0;
    }

    /**
     * Validate provided $array is sequential.
     * Throw QueryBuilderException with $message if not.
     *
     * @param array $array
     * @param       $message
     */
    private function validateSequentialArray(array $array, $message)
    {
        if (!$this->checkSequentialArray($array))
        {
            $this->throwException($message);
        }
    }

    /**
     * Validate provided $needle exists in array $array.
     * Throw QueryBuilderException with $message if not.
     *
     * @param       $needle
     * @param array $array
     * @param       $message
     */
    private function validateExistsInArray($needle, array $array, $message)
    {
        if (
        !in_array(
            $needle,
            $array
        )
        )
        {
            $this->throwException($message);
        }
    }

    /**
     * Validate provided $needle not exists in array $array.
     * Throw QueryBuilderException with $message if not.
     *
     * @param       $needle
     * @param array $array
     * @param       $message
     */
    private function validateNotExistsInArray($needle, array $array, $message)
    {
        if (
        in_array(
            $needle,
            $array
        )
        )
        {
            $this->throwException($message);
        }
    }

    /**
     * Validate provided arrays have equal items count.
     * Throw QueryBuilderException with $message if not.
     *
     * @param array $array1
     * @param array $array2
     * @param       $message
     */
    private function validateEqualLengthOfArrays(
        array $array1,
        array $array2,
        $message
    ) {
        if (count($array1) !== count($array2))
        {
            $this->throwException($message);
        }
    }

    /**
     * Validate provided array length are equal to $length.
     * Throw QueryBuilderException with $message if not.
     *
     * @param array $array
     * @param       $length
     * @param       $message
     */
    private function validateArrayLengthEqualTo(array $array, $length, $message)
    {
        if (count($array) !== $length)
        {
            $this->throwException($message);
        }
    }

    /**
     * Validate provided array length less or equal to $length.
     * Throw QueryBuilderException with $message if not.
     *
     * @param array $array
     * @param       $length
     * @param       $message
     */
    private function validateArrayLengthLessOrEqual(
        array $array,
        $length,
        $message
    ) {
        if (count($array) > $length)
        {
            $this->throwException($message);
        }
    }

    /**
     * Validate provided $var is not null value.
     * Throw QueryBuilderException with $message if not.
     *
     * @param $var
     * @param $message
     */
    private function validateNotNull($var, $message)
    {
        if (null === $var)
        {
            $this->throwException(
                $message
            );
        }
    }

    /**
     * Validate provided $var is null value.
     * Throw QueryBuilderException with $message if not.
     *
     * @param $var
     * @param $message
     */
    private function validateNull($var, $message)
    {
        if (null !== $var)
        {
            $this->throwException(
                $message
            );
        }
    }

    /**
     * Validate provided $string length not equal 0.
     * Throw QueryBuilderException with $message if not.
     *
     * @param $string
     * @param $message
     */
    private function validateNotEmptyString($string, $message)
    {
        if (strlen($string) === 0)
        {
            $this->throwException($message);
        }
    }
}