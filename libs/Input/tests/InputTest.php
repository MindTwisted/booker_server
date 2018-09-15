<?php

require_once 'libs/Input/Input.php';

use libs\Input\Input;

class TestsInput extends PHPUnit_Framework_TestCase
{
    /**
     * Test collect input from request with GET method
     */
    public function testCollectGetInput()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['name'] = 'John';
        $_GET['role'] = 'admin';

        Input::collectInput();

        $this->assertEquals(['name' => 'John',
                             'role' => 'admin'], Input::all());

        $this->assertEquals('John', Input::get('name'));
        $this->assertEquals('admin', Input::get('role'));

        $this->assertEquals(['name' => 'John'], Input::only(['name']));
    }

    /**
     * Test collect input from request with POST method
     */
    public function testCollectPostInput()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'John';
        $_POST['role'] = 'admin';

        Input::collectInput();

        $this->assertEquals(['name' => 'John',
                             'role' => 'admin'], Input::all());

        $this->assertEquals('John', Input::get('name'));
        $this->assertEquals('admin', Input::get('role'));

        $this->assertEquals(['name' => 'John'], Input::only(['name']));
    }

    /**
     * Test collect input from request with PUT method
     */
    public function testCollectPutInput()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_PUT['name'] = 'John';
        $_PUT['role'] = 'admin';

        Input::collectInput();

        $this->assertEquals(['name' => 'John',
                             'role' => 'admin'], Input::all());

        $this->assertEquals('John', Input::get('name'));
        $this->assertEquals('admin', Input::get('role'));

        $this->assertEquals(['name' => 'John'], Input::only(['name']));
    }

    /**
     * Test collect input from request with DELETE method
     */
    public function testCollectDeleteInput()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_DELETE['name'] = 'John';
        $_DELETE['role'] = 'admin';

        Input::collectInput();

        $this->assertEquals(['name' => 'John',
                             'role' => 'admin'], Input::all());

        $this->assertEquals('John', Input::get('name'));
        $this->assertEquals('admin', Input::get('role'));

        $this->assertEquals(['name' => 'John'], Input::only(['name']));
    }
}