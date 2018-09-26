<?php

require_once './phpunit';

require_once 'app/models/Model.php';
require_once 'app/models/UsersModel.php';
require_once 'app/models/EventsModel.php';
require_once 'app/controllers/UsersController.php';

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

use \app\models\Model;
use \app\controllers\UsersController;

class UsersControllerTest extends TestCase
{
    private static $usersController;
    private static $queryBuilder;
    private static $url = 'http://192.168.0.15/~user5/booker-server';

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

        $prefix = DB_TABLE_PREFIX;

        Model::setBuilder(self::$queryBuilder);
        Model::setDbPrefix("test_{$prefix}");

        self::$usersController = new UsersController();

        self::$queryBuilder->raw("DROP TABLE IF EXISTS test_{$prefix}users");

        self::$queryBuilder->raw(
            "CREATE TABLE test_{$prefix}users (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        role ENUM('admin', 'user') DEFAULT 'user',
                        is_active BOOLEAN DEFAULT 1,
                        UNIQUE (email)
                    )"
        );

        self::$queryBuilder->table("test_{$prefix}users")
            ->fields(['name', 'email', 'password', 'role', 'is_active'])
            ->values(
                ['John Walker', 'john@example.com', password_hash('secret', PASSWORD_BCRYPT), 'admin', 1],
                ['Michael Smith', 'smith@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user', 1],
                ['William Johnson', 'william@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user', 1],
                ['Tom Smith', 'tom@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user', 0]
                )
            ->insert()
            ->run();
    }

    public static function tearDownAfterClass()
    {
        $prefix = DB_TABLE_PREFIX;

        self::$queryBuilder->raw("DROP TABLE IF EXISTS test_{$prefix}users");
    }

    public function testIndex()
    {
        $httpClient = new HttpClient();

        $response = $httpClient->get(self::$url . '/api/users')->json();

        var_dump($response);
    }
}