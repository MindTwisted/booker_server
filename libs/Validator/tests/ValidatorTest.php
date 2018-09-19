<?php

require_once 'libs/QueryBuilder/config.php';
require_once 'libs/Validator/Validator.php';
require_once 'libs/Input/Input.php';
require_once 'libs/QueryBuilder/src/exception/QueryBuilderException.php';
require_once 'libs/QueryBuilder/src/traits/Validators.php';
require_once 'libs/QueryBuilder/src/QueryBuilder.php';

use libs\Validator\Validator;
use libs\Input\Input;
use libs\QueryBuilder\src\QueryBuilder;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * QueryBuilder instance
     */
    protected static $builder;
    
    /**
     * Set before tests
     */
    public static function setUpBeforeClass()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_POST['emptyName'] = '';
        $_POST['notEmptyName'] = 'John';

        $_POST['numericValue1'] = '123';
        $_POST['numericValue2'] = '123.25';
        $_POST['numericValue3'] = 123;
        $_POST['numericValue4'] = 123.25;
        $_POST['notNumericValue1'] = 'abc23';
        $_POST['notNumericValue2'] = '25sds';

        $_POST['integerValue1'] = '123';
        $_POST['integerValue2'] = 123;
        $_POST['notIntegerValue1'] = '123.23';
        $_POST['notIntegerValue2'] = 123.23;

        $_POST['valueFive'] = 5;
        $_POST['stringLengthFive'] = 'hello';

        $_POST['validEmail'] = 'john@example.com';
        $_POST['invalidEmail1'] = 'abcdc.com';
        $_POST['invalidEmail2'] = 'abcdc@sm';
        $_POST['invalidEmail3'] = 'abcdc@sm.';

        $_POST['valueCash'] = 'cash';

        $_POST['validAlphaDash'] = 'Harry_Potter and the philosophers-stone 1';
        $_POST['invalidAlphaDash1'] = 'Harry Potter & the philosophers stone';
        $_POST['invalidAlphaDash2'] = 'Harry Potter the philosophers stone!';
        $_POST['invalidAlphaDash3'] = 'Harry Potter the philosophers stone (1)';

        $_POST['uniqueName'] = 'Michael';
        $_POST['notUniqueName'] = 'John';

        Input::collectInput();

        self::$builder = new QueryBuilder(
            'mysql',
            MYSQL_SETTINGS['host'],
            MYSQL_SETTINGS['port'],
            MYSQL_SETTINGS['database'],
            MYSQL_SETTINGS['user'],
            MYSQL_SETTINGS['password']
        );

        self::$builder->raw('DROP TABLE IF EXISTS authors');
        self::$builder->raw(
            "CREATE TABLE IF NOT EXISTS authors (
                  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  first_name VARCHAR(255),
                  last_name VARCHAR(255)
            )"
        );
        self::$builder->raw(
            "INSERT INTO authors (first_name, last_name) 
             VALUES 
             ('Matthew', 'Johnson'),
             ('Tony', 'Fortune'),
             ('John', 'James'),
             ('William', 'Robinson')"
        );

        Validator::setBuilder(self::$builder);
    }

    /**
     * Clear after tests
     */
    public static function tearDownAfterClass()
    {
        self::$builder->raw('DROP TABLE IF EXISTS authors');
        self::$builder = null;
    }

    /**
     * Test Validator required rule pass
     */
    public function testValidateRequiredPass()
    {
        $validator = Validator::make(['notEmptyName' => 'required']);

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator required rule fail
     */
    public function testValidateRequiredFail()
    {
        $validator = Validator::make(['emptyName' => 'required']);

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(1, $validator->errors()['emptyName']);
    }

    /**
     * Test Validator numeric rule pass
     */
    public function testValidateNumericPass()
    {
        $validator = Validator::make(
            [
                'numericValue1' => 'numeric',
                'numericValue2' => 'numeric',
                'numericValue3' => 'numeric',
                'numericValue4' => 'numeric',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator numeric rule fail
     */
    public function testValidateNumericFail()
    {
        $validator = Validator::make(
            [
                'notNumericValue1' => 'numeric',
                'notNumericValue2' => 'numeric',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(2, $validator->errors());
        $this->assertCount(1, $validator->errors()['notNumericValue1']);
        $this->assertCount(1, $validator->errors()['notNumericValue2']);
    }

    /**
     * Test Validator integer rule pass
     */
    public function testValidateIntegerPass()
    {
        $validator = Validator::make(
            [
                'integerValue1' => 'integer',
                'integerValue2' => 'integer',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator integer rule fail
     */
    public function testValidateIntegerFail()
    {
        $validator = Validator::make(
            [
                'notIntegerValue1' => 'integer',
                'notIntegerValue2' => 'integer',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(2, $validator->errors());
        $this->assertCount(1, $validator->errors()['notIntegerValue1']);
        $this->assertCount(1, $validator->errors()['notIntegerValue2']);
    }

    /**
     * Test Validator min rule pass
     */
    public function testValidateMinPass()
    {
        $validator = Validator::make(
            [
                'valueFive' => 'min:1',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator min rule fail
     */
    public function testValidateMinFail()
    {
        $validator = Validator::make(
            [
                'valueFive' => 'min:10',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(1, $validator->errors()['valueFive']);
    }

    /**
     * Test Validator max rule pass
     */
    public function testValidateMaxPass()
    {
        $validator = Validator::make(
            [
                'valueFive' => 'max:10',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator max rule fail
     */
    public function testValidateMaxFail()
    {
        $validator = Validator::make(
            [
                'valueFive' => 'max:4',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(1, $validator->errors()['valueFive']);
    }

    /**
     * Test Validator minLength rule pass
     */
    public function testValidateMinLengthPass()
    {
        $validator = Validator::make(
            [
                'stringLengthFive' => 'min_length:5',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator minLength rule fail
     */
    public function testValidateMinLengthFail()
    {
        $validator = Validator::make(
            [
                'stringLengthFive' => 'min_length:6',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(1, $validator->errors()['stringLengthFive']);
    }

    /**
     * Test Validator email rule pass
     */
    public function testValidateEmailPass()
    {
        $validator = Validator::make(
            [
                'validEmail' => 'email',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator email rule fail
     */
    public function testValidateEmailFail()
    {
        $validator = Validator::make(
            [
                'invalidEmail1' => 'email',
                'invalidEmail2' => 'email',
                'invalidEmail3' => 'email',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(3, $validator->errors());
        $this->assertCount(1, $validator->errors()['invalidEmail1']);
        $this->assertCount(1, $validator->errors()['invalidEmail2']);
        $this->assertCount(1, $validator->errors()['invalidEmail3']);
    }

    /**
     * Test Validator included rule pass
     */
    public function testValidateIncludedPass()
    {
        $validator = Validator::make(
            [
                'valueCash' => 'included:(cash,credit_card)',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator included rule fail
     */
    public function testValidateIncludedFail()
    {
        $validator = Validator::make(
            [
                'valueCash' => 'included:(paypal,credit_card)',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(1, $validator->errors()['valueCash']);
    }

    /**
     * Test Validator alpha_dash rule pass
     */
    public function testValidateAlphaDashPass()
    {
        $validator = Validator::make(
            [
                'validAlphaDash' => 'alpha_dash',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator alpha_dash rule fail
     */
    public function testValidateAlphaDashFail()
    {
        $validator = Validator::make(
            [
                'invalidAlphaDash1' => 'alpha_dash',
                'invalidAlphaDash2' => 'alpha_dash',
                'invalidAlphaDash3' => 'alpha_dash',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(3, $validator->errors());
        $this->assertCount(1, $validator->errors()['invalidAlphaDash1']);
        $this->assertCount(1, $validator->errors()['invalidAlphaDash2']);
        $this->assertCount(1, $validator->errors()['invalidAlphaDash3']);
    }

    /**
     * Test Validator unique rule pass
     */
    public function testValidateUniquePass()
    {
        $validator = Validator::make(
            [
                'uniqueName' => 'unique:authors:first_name',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator unique rule fail
     */
    public function testValidateUniqueFail()
    {
        $validator = Validator::make(
            [
                'notUniqueName' => 'unique:authors:first_name',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(1, $validator->errors()['notUniqueName']);
    }

    /**
     * Test Validator exists rule pass
     */
    public function testValidateExistsPass()
    {
        $validator = Validator::make(
            [
                'notUniqueName' => 'exists:authors:first_name',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    /**
     * Test Validator exists rule fail
     */
    public function testValidateExistsFail()
    {
        $validator = Validator::make(
            [
                'uniqueName' => 'exists:authors:first_name',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(1, $validator->errors()['uniqueName']);
    }

    /**
     * Test Validator validate multiple rules at once
     */
    public function testValidateMultipleRules()
    {
        $validator = Validator::make(
            [
                'notEmptyName' => 'required|min_length:20|numeric|email|unique:authors:first_name',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(4, $validator->errors()['notEmptyName']);
    }

    /**
     * Test Validator validate values provided as separate array
     */
    public function testValidateProvidedValues()
    {
        $validator = Validator::make(
            [
                'notEmptyName' => 'john@example.com'
            ],
            [
                'notEmptyName' => 'required|min_length:20|numeric|email|unique:authors:first_name',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(2, $validator->errors()['notEmptyName']);
    }
    
}