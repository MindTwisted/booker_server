<?php

require_once './phpunit';

require_once 'app/models/Model.php';
require_once 'app/models/UsersModel.php';

require_once 'libs/QueryBuilder/src/exception/QueryBuilderException.php';
require_once 'libs/QueryBuilder/src/traits/Validators.php';
require_once 'libs/QueryBuilder/src/QueryBuilder.php';
require_once 'libs/Env/Env.php';

\libs\Env\Env::setEnvFromFile('./.env');

require_once 'app/config/config.php';

use \PHPUnit\Framework\TestCase;
use \app\models\Model;
use \app\models\UsersModel;
use \libs\QueryBuilder\src\QueryBuilder;

class UsersModelTest extends TestCase
{
    private static $usersModel;
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

        $prefix = DB_TABLE_PREFIX;

        Model::setBuilder(self::$queryBuilder);
        Model::setDbPrefix("test_{$prefix}");

        self::$usersModel = new UsersModel();

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

    public function testGetAllUsers()
    {
        $users = self::$usersModel->getUsers(null, false);

        $this->assertCount(4, $users);
    }

    public function testGetActiveUsers()
    {
        $users = self::$usersModel->getUsers(null);

        $this->assertCount(3, $users);
    }

    public function testGetUserById()
    {
        $user = self::$usersModel->getUsers(2);

        $this->assertCount(1, $user);
        $this->assertEquals('Michael Smith', $user[0]['name']);
        $this->assertEquals('smith@example.com', $user[0]['email']);
    }

    public function testAddUser()
    {
        $userId = self::$usersModel->addUser('Jimmi Smith', 'jimmi@example.com', 'secret');
        
        $this->assertEquals(5, $userId);

        $addedUser = self::$usersModel->getUsers(5);

        $this->assertCount(1, $addedUser);
        $this->assertEquals('Jimmi Smith', $addedUser[0]['name']);
        $this->assertEquals('jimmi@example.com', $addedUser[0]['email']);
    }

    public function testUpdateUser()
    {
        self::$usersModel->updateUser(5, 'Jimmi', 'jm@example.com');

        $updatedUser = self::$usersModel->getUsers(5);

        $this->assertCount(1, $updatedUser);
        $this->assertEquals('Jimmi', $updatedUser[0]['name']);
        $this->assertEquals('jm@example.com', $updatedUser[0]['email']);
    }

    public function testDeleteUser()
    {
        $initialUsers = self::$usersModel->getUsers(null);

        $this->assertCount(4, $initialUsers);

        self::$usersModel->deleteUser(5);

        $updatedUsers = self::$usersModel->getUsers(null);

        $this->assertCount(3, $updatedUsers);
    }
}