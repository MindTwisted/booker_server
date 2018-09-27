<?php

require_once './phpunit';

require_once 'app/models/Model.php';
require_once 'app/models/RoomsModel.php';
require_once 'app/models/EventsModel.php';

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

class RoomsControllerTest extends TestCase
{
    private static $queryBuilder;
    private static $adminToken;
    private static $userToken;

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

        self::$queryBuilder->raw("SET FOREIGN_KEY_CHECKS=0");
        self::$queryBuilder->raw("DROP TABLE IF EXISTS {$prefix}rooms");
        self::$queryBuilder->raw("DROP TABLE IF EXISTS {$prefix}users");
        self::$queryBuilder->raw("DROP TABLE IF EXISTS {$prefix}events");
        self::$queryBuilder->raw("SET FOREIGN_KEY_CHECKS=1");

        self::$queryBuilder->raw(
            "CREATE TABLE {$prefix}rooms (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        is_active BOOLEAN DEFAULT 1,
                        UNIQUE (name)
                    )"
        );

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

        self::$queryBuilder->raw(
            "CREATE TABLE {$prefix}events (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        recur_id BIGINT UNSIGNED DEFAULT NULL,
                        description TEXT NOT NULL,
                        start_time TIMESTAMP NOT NULL DEFAULT '1980-01-01 00:00:00',
                        end_time TIMESTAMP NOT NULL DEFAULT '1980-01-01 00:00:00',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        user_id INT UNSIGNED,
                        room_id INT UNSIGNED,
                        FOREIGN KEY (user_id)
                            REFERENCES {$prefix}users (id)
                            ON DELETE SET NULL,
                        FOREIGN KEY (room_id)
                            REFERENCES {$prefix}rooms (id)
                            ON DELETE SET NULL
                    )"
        );

        self::$queryBuilder->table("{$prefix}rooms")
            ->fields(['name', 'is_active'])
            ->values(
                ['Boardroom 1', 1],
                ['Boardroom 2', 1],
                ['Boardroom 3', 1],
                ['Boardroom 4', 1],
                ['Boardroom 5', 0]
                )
            ->insert()
            ->run();

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
        
        self::$queryBuilder->table("{$prefix}events")
            ->fields(['description', 'start_time', 'end_time', 'user_id', 'room_id', 'recur_id'])
            ->values(
                ['Meeting', '2018-09-18 09:00:01', '2018-09-18 10:59:59', 2, 1, NULL],
                ['Meeting', '2018-09-18 11:00:01', '2018-09-18 13:59:59', 2, 1, NULL],
                ['Meeting Number Three', '2018-09-18 15:00:01', '2018-09-18 18:59:59', 2, 1, NULL],
                ['Meeting', '2018-09-18 09:00:01', '2018-09-18 10:59:59', 2, 2, NULL],
                ['Meeting', '2018-09-27 10:30:01', '2018-09-27 11:29:59', 2, 3, NULL],
                ['Meeting', '2018-09-27 10:30:01', '2018-09-27 11:29:59', 3, 1, NULL],
                ['Meeting', '2018-10-04 10:30:01', '2018-10-04 11:29:59', 2, 4, NULL],
                ['Meeting', '2018-10-04 10:30:01', '2018-10-04 11:29:59', 3, 2, NULL],
                ['Meeting', '2018-10-11 10:30:01', '2018-10-11 11:29:59', 2, 1, NULL],
                ['Meeting', '2018-10-11 10:30:01', '2018-10-11 11:29:59', 3, 3, NULL],
                ['Meeting', '2018-10-18 12:00:01', '2018-10-18 14:59:59', 3, 1, NULL],
                ['Meeting', '2018-10-18 12:00:01', '2018-10-18 14:59:59', 2, 2, NULL],
                ['Meeting', '2018-10-18 09:00:01', '2018-10-18 10:59:59', 1, 4, NULL],
                ['Meeting', '2018-10-19 09:00:01', '2018-10-19 10:59:59', 1, 2, 123456],
                ['Meeting', '2018-10-26 09:00:01', '2018-10-26 10:59:59', 1, 2, 123456],
                ['Meeting', '2018-11-02 09:00:01', '2018-11-02 10:59:59', 1, 2, 123456]
              )
            ->insert()
            ->run();

        self::$adminToken = HttpClient::post(ROOT_URL . '/api/auth', [
                'email' => 'john@example.com',
                'password' => 'secret'
            ])->jsonToArray()['message']['data']['token'];
    
        self::$userToken = HttpClient::post(ROOT_URL . '/api/auth', [
                'email' => 'smith@example.com',
                'password' => 'secret'
            ])->jsonToArray()['message']['data']['token'];
    }

    public static function tearDownAfterClass()
    {
        $prefix = DB_TABLE_TEST_PREFIX;

        self::$queryBuilder->raw("SET FOREIGN_KEY_CHECKS=0");
        self::$queryBuilder->raw("DROP TABLE IF EXISTS {$prefix}rooms");
        self::$queryBuilder->raw("DROP TABLE IF EXISTS {$prefix}users");
        self::$queryBuilder->raw("DROP TABLE IF EXISTS {$prefix}events");
        self::$queryBuilder->raw("SET FOREIGN_KEY_CHECKS=1");
    }

    public function testOnlyAuthCanGetRoomsList()
    {
        $adminToken = self::$adminToken;
        $userToken = self::$userToken;

        // Request as not authorized
        $response = HttpClient::get(ROOT_URL . '/api/rooms');

        $this->assertEquals(401, $response->code());

        // Request with user permission
        $response = HttpClient::get(ROOT_URL . '/api/rooms', [
            "Authorization: Bearer {$userToken}"
        ]);

        $this->assertEquals(200, $response->code());

        // Request with admin permission
        $response = HttpClient::get(ROOT_URL . '/api/rooms', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(200, $response->code());
    }

    public function testCanGetOnlyActiveRooms()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::get(ROOT_URL . '/api/rooms', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(4, $data);
    }

    public function testCanGetRoomById()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::get(ROOT_URL . '/api/rooms/1', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('Boardroom 1', $data[0]['name']);
    }

    public function test404ResponseNotActiveRoom()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::get(ROOT_URL . '/api/rooms/5', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $code = $response->code();

        $this->assertEquals(404, $code);
    }

    public function test404ResponseNotExistsRoom()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::get(ROOT_URL . '/api/rooms/33', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $code = $response->code();

        $this->assertEquals(404, $code);
    }

    public function testOnlyAdminCanCreateRoom()
    {
        $adminToken = self::$adminToken;
        $userToken = self::$userToken;

        // Request as not authorized
        $response = HttpClient::post(ROOT_URL . '/api/rooms', []);

        $this->assertEquals(401, $response->code());

        // Request with user permission
        $response = HttpClient::post(ROOT_URL . '/api/rooms', [], [
            "Authorization: Bearer {$userToken}"
        ]);

        $this->assertEquals(403, $response->code());

        // Request with admin permission
        $response = HttpClient::post(ROOT_URL . '/api/rooms', [], [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(422, $response->code());
    }

    public function testCreateRoom()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::post(ROOT_URL . '/api/rooms', [
            'name' => 'New Meeting Room'
        ], [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(200, $response->code());

        $response = HttpClient::get(ROOT_URL . '/api/rooms/6', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('New Meeting Room', $data[0]['name']);
    }

    public function testUpdateRoom()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::put(ROOT_URL . '/api/rooms/6', [
            'name' => 'New Meeting Room UPDATED'
        ], [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(200, $response->code());

        $response = HttpClient::get(ROOT_URL . '/api/rooms/6', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('New Meeting Room UPDATED', $data[0]['name']);
    }

    public function testDeleteRoom()
    {
        $adminToken = self::$adminToken;

        // Get initial rooms list
        $response = HttpClient::get(ROOT_URL . '/api/rooms', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(5, $data);

        // Delete room
        $response = HttpClient::delete(ROOT_URL . '/api/rooms/6', [], [
            "Authorization: Bearer {$adminToken}"
        ]);
        
        $this->assertEquals(200, $response->code());

        // Get rooms list after delete
        $response = HttpClient::get(ROOT_URL . '/api/rooms', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(4, $data);
    }

    public function testRoomWithComingEventsCanNotBeDeleted()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::delete(ROOT_URL . '/api/rooms/1', [], [
            "Authorization: Bearer {$adminToken}"
        ]);
        
        $this->assertEquals(409, $response->code());
    }
}