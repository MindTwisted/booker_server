<?php

require_once './phpunit';

require_once 'libs/QueryBuilder/src/exception/QueryBuilderException.php';
require_once 'libs/QueryBuilder/src/traits/Validators.php';
require_once 'libs/QueryBuilder/src/QueryBuilder.php';
require_once 'libs/Env/Env.php';
require_once 'libs/HttpClient.php';

\libs\Env\Env::setEnvFromFile('./.env');

require_once 'app/config/config.php';

use \PHPUnit\Framework\TestCase;

use \libs\QueryBuilder\src\QueryBuilder;
use \libs\HttpClient;

class AuthControllerTest extends TestCase
{
    private static $queryBuilder;

    public static function setUpBeforeClass()
    {
        self::$queryBuilder = new QueryBuilder(
            'mysql',
            DB_HOST,
            DB_PORT,
            DB_DATABASE,
            DB_USER,
            DB_PASSWORD
        );

        $prefix = DB_TABLE_TEST_PREFIX;

        self::$queryBuilder->raw("DROP TABLE IF EXISTS {$prefix}users");
        
        self::$queryBuilder->raw(
            "CREATE TABLE {$prefix}users (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        role ENUM('admin', 'user') DEFAULT 'user',
                        is_active BOOLEAN DEFAULT 1,
                        UNIQUE (email)
                    )"
        );

        self::$queryBuilder->table("{$prefix}users")
            ->fields(['name', 'email', 'password', 'role', 'is_active'])
            ->values(
                ['John Walker 111', 'john@example.com', password_hash('secret', PASSWORD_BCRYPT), 'admin', 1],
                ['Michael Smith', 'smith@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user', 1],
                ['William Johnson', 'william@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user', 0],
                ['Tom Smith', 'tom@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user', 0]
                )
            ->insert()
            ->run();
    }

    public static function tearDownAfterClass()
    {
        $prefix = DB_TABLE_TEST_PREFIX;

        self::$queryBuilder->raw("DROP TABLE IF EXISTS {$prefix}users");
    }

    public function testUserCanLogin()
    {
        $response = HttpClient::post(ROOT_URL . '/api/auth', [
            'email' => 'john@example.com',
            'password' => 'secret'
        ]);

        $this->assertEquals(200, $response->code());
        $this->assertEquals(
            "User 'John Walker 111' was successfully logged in.",
            $response->jsonToArray()['message']['text']
        );
    }

    public function testLoggedInUserCanGetAuthInfo()
    {
        $token = HttpClient::post(ROOT_URL . '/api/auth', [
            'email' => 'john@example.com',
            'password' => 'secret'
        ])->jsonToArray()['message']['data']['token'];

        $response = HttpClient::get(ROOT_URL . '/api/auth', [
            "Authorization: Bearer {$token}"
        ]);

        $this->assertEquals(200, $response->code());
        
        $data = $response->jsonToArray()['message']['data'];

        $this->assertEquals('john@example.com', $data['email']);
    }
}