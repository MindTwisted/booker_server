<?php

require_once 'libs/Validator/Validator.php';
require_once 'libs/Input/Input.php';

use libs\Validator\Validator;
use libs\Input\Input;

class TestsValidator extends PHPUnit_Framework_TestCase
{
    
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

        Input::collectInput();
    }

    public function testValidateRequiredPass()
    {
        $validator = Validator::make(['notEmptyName' => 'required']);

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    public function testValidateRequiredFail()
    {
        $validator = Validator::make(['emptyName' => 'required']);

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(1, $validator->errors()['emptyName']);
    }

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

    public function testValidateMinLengthPass()
    {
        $validator = Validator::make(
            [
                'stringLengthFive' => 'minLength:5',
            ]
        );

        $this->assertFalse($validator->fails());

        $this->assertCount(0, $validator->errors());
    }

    public function testValidateMinLengthFail()
    {
        $validator = Validator::make(
            [
                'stringLengthFive' => 'minLength:6',
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertCount(1, $validator->errors());
        $this->assertCount(1, $validator->errors()['stringLengthFive']);
    }

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
    
}