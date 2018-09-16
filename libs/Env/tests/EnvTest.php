<?php

require_once 'libs/Env/Env.php';

use libs\Env\Env;

class EnvTest extends PHPUnit_Framework_TestCase
{
    /**
     * Generate .env file for tests
     */
    public static function setUpBeforeClass()
    {
        file_put_contents('libs/Env/tests/.env', "NAME=John\nDATABASE=root\nENV=testing");

        Env::setEnvFromFile('libs/Env/tests/.env');
    }

    /**
     * Remove testing .env file
     */
    public static function tearDownAfterClass()
    {
        unlink('libs/Env/tests/.env');
    }

    /**
     * Test get particular variable from ENV
     */
    public function testGetParticularValueFromEnv()
    {
        $this->assertEquals('John', Env::get('NAME'));
        $this->assertEquals('root', Env::get('DATABASE'));
        $this->assertEquals('testing', Env::get('ENV'));
    }

    /**
     * Test get all data from ENV
     */
    public function testGetAllEnvValues()
    {
        $this->assertEquals(
        ['NAME' => 'John',
         'DATABASE' => 'root',
         'ENV' => 'testing'], 
        Env::getEnv());
    }
}