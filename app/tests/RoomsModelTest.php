<?php

require_once './phpunit';

require_once 'app/models/Model.php';
require_once 'app/models/RoomsModel.php';

require_once 'libs/QueryBuilder/src/exception/QueryBuilderException.php';
require_once 'libs/QueryBuilder/src/traits/Validators.php';
require_once 'libs/QueryBuilder/src/QueryBuilder.php';
require_once 'libs/Env/Env.php';

\libs\Env\Env::setEnvFromFile('./.env');

require_once 'app/config/config.php';

use \PHPUnit\Framework\TestCase;
use \app\models\Model;
use \app\models\RoomsModel;
use \libs\QueryBuilder\src\QueryBuilder;

class RoomsModelTest extends TestCase
{
    private static $roomsModel;
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

        self::$roomsModel = new RoomsModel();

        self::$queryBuilder->raw("DROP TABLE IF EXISTS test_{$prefix}rooms");

        self::$queryBuilder->raw(
            "CREATE TABLE test_{$prefix}rooms (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    is_active BOOLEAN DEFAULT 1,
                    UNIQUE (name)
                )"
        );

        self::$queryBuilder->table("test_{$prefix}rooms")
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
    }

    public static function tearDownAfterClass()
    {
        $prefix = DB_TABLE_PREFIX;

        self::$queryBuilder->raw("DROP TABLE IF EXISTS test_{$prefix}rooms");
    }

    public function testGetAllRooms()
    {
        $rooms = self::$roomsModel->getRooms(null, false);

        $this->assertCount(5, $rooms);
    }

    public function testGetActiveRooms()
    {
        $rooms = self::$roomsModel->getRooms(null);

        $this->assertCount(4, $rooms);
    }

    public function testGetRoomById()
    {
        $room = self::$roomsModel->getRooms(2);

        $this->assertCount(1, $room);
        $this->assertEquals('Boardroom 2', $room[0]['name']);
    }

    public function testAddRoom()
    {
        $roomId = self::$roomsModel->addRoom('Meetings Room');
        
        $this->assertEquals(6, $roomId);

        $addedRoom = self::$roomsModel->getRooms(6);

        $this->assertCount(1, $addedRoom);
        $this->assertEquals('Meetings Room', $addedRoom[0]['name']);
    }

    public function testUpdateRoom()
    {
        self::$roomsModel->updateRoom(6, 'Room For Meetings');

        $updatedRoom = self::$roomsModel->getRooms(6);

        $this->assertCount(1, $updatedRoom);
        $this->assertEquals('Room For Meetings', $updatedRoom[0]['name']);
    }

    public function testDeleteRoom()
    {
        $initialRooms = self::$roomsModel->getRooms(null);

        $this->assertCount(5, $initialRooms);

        self::$roomsModel->deleteRoom(6);

        $updatedRooms = self::$roomsModel->getRooms(null);

        $this->assertCount(4, $updatedRooms);
    }
}