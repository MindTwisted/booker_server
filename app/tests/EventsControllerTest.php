<?php

require_once './phpunit';

require_once 'app/models/Model.php';
require_once 'app/models/UsersModel.php';
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

class EventsControllerTest extends TestCase
{
    private static $queryBuilder;
    private static $adminToken;
    private static $userToken;
    private static $lastUpdatedRecurId;

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

    public function testOnlyAuthCanGetEventsList()
    {
        $adminToken = self::$adminToken;
        $userToken = self::$userToken;

        // Request as not authorized
        $response = HttpClient::get(ROOT_URL . '/api/events');

        $this->assertEquals(401, $response->code());

        // Request with user permission
        $response = HttpClient::get(ROOT_URL . '/api/events', [
            "Authorization: Bearer {$userToken}"
        ]);

        $this->assertEquals(200, $response->code());

        // Request with admin permission
        $response = HttpClient::get(ROOT_URL . '/api/events', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(200, $response->code());
    }

    public function testCanGetAllEvents()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::get(ROOT_URL . '/api/events', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(16, $data);
    }

    public function testCanGetEventsByUserId()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::get(ROOT_URL . '/api/events?user_id=1', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(4, $data);
    }

    public function testCanGetEventsByRoomId()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::get(ROOT_URL . '/api/events?room_id=2', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(6, $data);
    }

    public function testCanGetEventsByTimeRange()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::get(ROOT_URL . '/api/events?start_time=gt:2018-10-01&end_time=lt:2018-11-01', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(9, $data);
    }

    public function testCanGetEventById()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::get(ROOT_URL . '/api/events/3', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('Meeting Number Three', $data[0]['description']);
    }

    public function test404ResponseNotExistsEvent()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::get(ROOT_URL . '/api/events/33', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $code = $response->code();

        $this->assertEquals(404, $code);
    }

    public function testOnlyAuthCanCreateEvent()
    {
        $adminToken = self::$adminToken;
        $userToken = self::$userToken;

        // Request as not authorized
        $response = HttpClient::post(ROOT_URL . '/api/events', []);

        $this->assertEquals(401, $response->code());

        // Request with user permission
        $response = HttpClient::post(ROOT_URL . '/api/events', [], [
            "Authorization: Bearer {$userToken}"
        ]);

        $this->assertEquals(422, $response->code());

        // Request with admin permission
        $response = HttpClient::post(ROOT_URL . '/api/events', [], [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(422, $response->code());
    }

    public function testCreateSingleEvent()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::post(ROOT_URL . '/api/events', [
            'description' => 'NEW event',
            'user_id' => 1,
            'room_id' => 2,
            'start_time' => strtotime('2018-10-04 16:40:00'),
            'end_time' => strtotime('2018-10-04 17:40:00')
        ], [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(200, $response->code());

        $response = HttpClient::get(ROOT_URL . '/api/events/17', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('NEW event', $data[0]['description']);
    }

    public function testCreateMultipleEvents()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::post(ROOT_URL . '/api/events', [
            'description' => 'Multiple event',
            'user_id' => 1,
            'room_id' => 2,
            'start_time' => strtotime('2018-10-05 16:40:00'),
            'end_time' => strtotime('2018-10-05 17:40:00'),
            'recur_type' => 'weekly',
            'recur_duration' => 2
        ], [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(200, $response->code());

        $response = HttpClient::get(ROOT_URL . '/api/events/18', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('Multiple event', $data[0]['description']);

        $response = HttpClient::get(ROOT_URL . '/api/events/19', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('Multiple event', $data[0]['description']);

        $response = HttpClient::get(ROOT_URL . '/api/events/20', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('Multiple event', $data[0]['description']);
    }

    public function testUpdateSingleEvent()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::put(ROOT_URL . '/api/events/20', [
            'description' => 'UPDATED event',
            'user_id' => 2,
            'room_id' => 3,
            'start_time' => strtotime('2018-10-04 16:40:00'),
            'end_time' => strtotime('2018-10-04 17:40:00')
        ], [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(200, $response->code());

        $response = HttpClient::get(ROOT_URL . '/api/events/20', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('UPDATED event', $data[0]['description']);
    }

    public function testUpdateMultipleEvents()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::put(ROOT_URL . '/api/events/14', [
            'description' => 'UPDATED MULTIPLE event',
            'user_id' => 2,
            'room_id' => 3,
            'start_time' => strtotime('2018-11-05 16:40:00'),
            'end_time' => strtotime('2018-11-05 17:40:00'),
            'recur_id' => 123456
        ], [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(200, $response->code());
        self::$lastUpdatedRecurId = $response->jsonToArray()['message']['data']['recurId'];

        $response = HttpClient::get(ROOT_URL . '/api/events/14', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('UPDATED MULTIPLE event', $data[0]['description']);

        $response = HttpClient::get(ROOT_URL . '/api/events/15', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('UPDATED MULTIPLE event', $data[0]['description']);

        $response = HttpClient::get(ROOT_URL . '/api/events/16', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $data = $response->jsonToArray()['message']['data'];

        $this->assertCount(1, $data);
        $this->assertEquals('UPDATED MULTIPLE event', $data[0]['description']);
    }

    public function testDeleteSingleEvent()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::delete(ROOT_URL . '/api/events/20', [], [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(200, $response->code());

        $response = HttpClient::get(ROOT_URL . '/api/events/20', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(404, $response->code());
    }

    public function testDeleteMultipleEvents()
    {
        $adminToken = self::$adminToken;

        $response = HttpClient::delete(ROOT_URL . '/api/events/14', [
            'recur_id' => self::$lastUpdatedRecurId
        ], [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(200, $response->code());

        $response = HttpClient::get(ROOT_URL . '/api/events/14', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(404, $response->code());

        $response = HttpClient::get(ROOT_URL . '/api/events/15', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(404, $response->code());

        $response = HttpClient::get(ROOT_URL . '/api/events/16', [
            "Authorization: Bearer {$adminToken}"
        ]);

        $this->assertEquals(404, $response->code());
    }

}