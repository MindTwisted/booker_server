<?php

require_once './phpunit';

require_once 'app/models/Model.php';
require_once 'app/models/EventsModel.php';

require_once 'libs/QueryBuilder/src/exception/QueryBuilderException.php';
require_once 'libs/QueryBuilder/src/traits/Validators.php';
require_once 'libs/QueryBuilder/src/QueryBuilder.php';
require_once 'libs/Env/Env.php';

\libs\Env\Env::setEnvFromFile('./.env');

require_once 'app/config/config.php';

use \PHPUnit\Framework\TestCase;
use \app\models\Model;
use \app\models\EventsModel;
use \libs\QueryBuilder\src\QueryBuilder;

class EventsModelTest extends TestCase
{
    private static $eventsModel;
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

        Model::setBuilder(self::$queryBuilder);
        Model::setDbPrefix($prefix);

        self::$eventsModel = new EventsModel();

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
                ['John Walker', 'john@example.com', password_hash('secret', PASSWORD_BCRYPT), 'admin', 1],
                ['Michael Smith', 'smith@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user', 1],
                ['William Johnson', 'william@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user', 1],
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

    public function testGetEventsByTimestamps()
    {
        $timestamps = [
            [
                'startTime' => strtotime('2018-09-18 08:00:00'),
                'endTime' => strtotime('2018-09-18 12:00:00'),
            ],
            [
                'startTime' => strtotime('2018-10-18 11:00:00'),
                'endTime' => strtotime('2018-10-18 13:00:00'),
            ]
        ];

        $events = self::$eventsModel->getEventsByTimestamps(1, $timestamps);

        $this->assertCount(3, $events);
    }

    public function testGetEventsByRecurId()
    {
        $events = self::$eventsModel->getEventsByRecurId(14, 123456);

        $this->assertCount(3, $events);
    }

    public function testGetAllEvents()
    {
        $events = self::$eventsModel->getEvents();

        $this->assertCount(16, $events);
    }

    public function testGetEventById()
    {
        $event = self::$eventsModel->getEvents(3);

        $this->assertCount(1, $event);
        $this->assertEquals('Meeting Number Three', $event[0]['description']);
    }

    public function testGetEventsByUserId()
    {
        $events = self::$eventsModel->getEvents(null, [
            'user_id' => 2
        ]);

        $this->assertCount(8, $events);
    }

    public function testGetEventsByRoomId()
    {
        $events = self::$eventsModel->getEvents(null, [
            'room_id' => 2
        ]);

        $this->assertCount(6, $events);
    }

    public function testGetEventsByTimeRange()
    {
        $events = self::$eventsModel->getEvents(null, [
            'start_time' => "gt:2018-09-01",
            'end_time' => 'lt:2018-10-01'
        ]);

        $this->assertCount(6, $events);
    }

    public function testAddSingleEvent()
    {
        $timestamps = [
            [
                'startTime' => strtotime('2018-09-27 08:00:00'),
                'endTime' => strtotime('2018-09-27 12:00:00'),
            ]
        ];

        $initialEvents = self::$eventsModel->getEvents();

        $this->assertCount(16, $initialEvents);

        self::$eventsModel->addEvent(1, 2, 'Single event', $timestamps);

        $updatedEvents = self::$eventsModel->getEvents();

        $this->assertCount(17, $updatedEvents);

        $addedEvent = self::$eventsModel->getEvents(17);

        $this->assertEquals('Single event', $addedEvent[0]['description']);
        $this->assertEquals(1, $addedEvent[0]['user']['id']);
        $this->assertEquals(2, $addedEvent[0]['room']['id']);
    }

    public function testAddMultipleEvents()
    {
        sleep(1);

        $timestamps = [
            [
                'startTime' => strtotime('2018-09-27 08:00:00'),
                'endTime' => strtotime('2018-09-27 12:00:00'),
            ],
            [
                'startTime' => strtotime('2018-10-27 08:00:00'),
                'endTime' => strtotime('2018-10-27 12:00:00'),
            ]
        ];

        $initialEvents = self::$eventsModel->getEvents();

        $this->assertCount(17, $initialEvents);

        self::$eventsModel->addEvent(1, 2, 'Multiple event', $timestamps);

        $updatedEvents = self::$eventsModel->getEvents();

        $this->assertCount(19, $updatedEvents);

        $addedEvent1 = self::$eventsModel->getEvents(18);
        $addedEvent2 = self::$eventsModel->getEvents(19);

        $this->assertEquals('Multiple event', $addedEvent1[0]['description']);
        $this->assertEquals(1, $addedEvent1[0]['user']['id']);
        $this->assertEquals(2, $addedEvent1[0]['room']['id']);

        $this->assertEquals('Multiple event', $addedEvent2[0]['description']);
        $this->assertEquals(1, $addedEvent2[0]['user']['id']);
        $this->assertEquals(2, $addedEvent2[0]['room']['id']);
    }

    public function testUpdateSingleEvent()
    {
        $initialStartTime = '2018-10-27 08:00:00';
        $initialEndTime = '2018-10-27 12:00:00';

        $updatedStartTime = '2018-11-27 08:00:00';
        $updatedEndTime = '2018-11-27 12:00:00';

        $updatedTimestamp = [
            'startTime' => strtotime($updatedStartTime),
            'endTime' => strtotime($updatedEndTime),
        ];

        $initialEvent = self::$eventsModel->getEvents(19);

        $this->assertEquals($initialStartTime, date('Y-m-d H:i:s', $initialEvent[0]['start_time']));
        $this->assertEquals($initialEndTime, date('Y-m-d H:i:s', $initialEvent[0]['end_time']));
        $this->assertEquals('Multiple event', $initialEvent[0]['description']);
        $this->assertEquals(1, $initialEvent[0]['user']['id']);
        $this->assertEquals(2, $initialEvent[0]['room']['id']);

        self::$eventsModel->updateSingleEvent(19, 2, 3, 'Updated single', $updatedTimestamp);

        $updatedEvent = self::$eventsModel->getEvents(19);

        $this->assertEquals($updatedStartTime, date('Y-m-d H:i:s', $updatedEvent[0]['start_time']));
        $this->assertEquals($updatedEndTime, date('Y-m-d H:i:s', $updatedEvent[0]['end_time']));
        $this->assertEquals('Updated single', $updatedEvent[0]['description']);
        $this->assertEquals(2, $updatedEvent[0]['user']['id']);
        $this->assertEquals(3, $updatedEvent[0]['room']['id']);
    }

    public function testUpdateMultipleEvents()
    {
        sleep(1);

        $initialStartTime1 = '2018-10-19 09:00:00';
        $initialEndTime1 = '2018-10-19 11:00:00';

        $initialStartTime2 = '2018-10-26 09:00:00';
        $initialEndTime2 = '2018-10-26 11:00:00';

        $initialStartTime3 = '2018-11-02 09:00:00';
        $initialEndTime3 = '2018-11-02 11:00:00';

        $updatedStartTime1 = '2018-11-27 08:00:00';
        $updatedEndTime1 = '2018-11-27 12:00:00';

        $updatedStartTime2 = '2018-12-07 08:00:00';
        $updatedEndTime2 = '2018-12-07 12:00:00';

        $updatedStartTime3 = '2018-12-27 08:00:00';
        $updatedEndTime3 = '2018-12-27 12:00:00';

        $updatedTimestamps = [
            [
                'startTime' => strtotime($updatedStartTime1),
                'endTime' => strtotime($updatedEndTime1)
            ],
            [
                'startTime' => strtotime($updatedStartTime2),
                'endTime' => strtotime($updatedEndTime2)
            ],
            [
                'startTime' => strtotime($updatedStartTime3),
                'endTime' => strtotime($updatedEndTime3)
            ]
        ];

        $initialEvent1 = self::$eventsModel->getEvents(14);
        $initialEvent2 = self::$eventsModel->getEvents(15);
        $initialEvent3 = self::$eventsModel->getEvents(16);

        $this->assertEquals($initialStartTime1, date('Y-m-d H:i:s', $initialEvent1[0]['start_time']));
        $this->assertEquals($initialEndTime1, date('Y-m-d H:i:s', $initialEvent1[0]['end_time']));
        $this->assertEquals('Meeting', $initialEvent1[0]['description']);
        $this->assertEquals(1, $initialEvent1[0]['user']['id']);
        $this->assertEquals(2, $initialEvent1[0]['room']['id']);

        $this->assertEquals($initialStartTime2, date('Y-m-d H:i:s', $initialEvent2[0]['start_time']));
        $this->assertEquals($initialEndTime2, date('Y-m-d H:i:s', $initialEvent2[0]['end_time']));
        $this->assertEquals('Meeting', $initialEvent2[0]['description']);
        $this->assertEquals(1, $initialEvent2[0]['user']['id']);
        $this->assertEquals(2, $initialEvent2[0]['room']['id']);

        $this->assertEquals($initialStartTime3, date('Y-m-d H:i:s', $initialEvent3[0]['start_time']));
        $this->assertEquals($initialEndTime3, date('Y-m-d H:i:s', $initialEvent3[0]['end_time']));
        $this->assertEquals('Meeting', $initialEvent3[0]['description']);
        $this->assertEquals(1, $initialEvent3[0]['user']['id']);
        $this->assertEquals(2, $initialEvent3[0]['room']['id']);

        $initialEvents = self::$eventsModel->getEventsByRecurId(14, 123456);

        $this->assertCount(3, $initialEvents);

        self::$eventsModel->updateMultipleEvents(2, 3, 'Updated multiple', $initialEvents, $updatedTimestamps);

        $updatedEvent1 = self::$eventsModel->getEvents(14);
        $updatedEvent2 = self::$eventsModel->getEvents(15);
        $updatedEvent3 = self::$eventsModel->getEvents(16);

        $this->assertEquals($updatedStartTime1, date('Y-m-d H:i:s', $updatedEvent1[0]['start_time']));
        $this->assertEquals($updatedEndTime1, date('Y-m-d H:i:s', $updatedEvent1[0]['end_time']));
        $this->assertEquals('Updated multiple', $updatedEvent1[0]['description']);
        $this->assertEquals(2, $updatedEvent1[0]['user']['id']);
        $this->assertEquals(3, $updatedEvent1[0]['room']['id']);

        $this->assertEquals($updatedStartTime2, date('Y-m-d H:i:s', $updatedEvent2[0]['start_time']));
        $this->assertEquals($updatedEndTime2, date('Y-m-d H:i:s', $updatedEvent2[0]['end_time']));
        $this->assertEquals('Updated multiple', $updatedEvent2[0]['description']);
        $this->assertEquals(2, $updatedEvent2[0]['user']['id']);
        $this->assertEquals(3, $updatedEvent2[0]['room']['id']);

        $this->assertEquals($updatedStartTime3, date('Y-m-d H:i:s', $updatedEvent3[0]['start_time']));
        $this->assertEquals($updatedEndTime3, date('Y-m-d H:i:s', $updatedEvent3[0]['end_time']));
        $this->assertEquals('Updated multiple', $updatedEvent3[0]['description']);
        $this->assertEquals(2, $updatedEvent3[0]['user']['id']);
        $this->assertEquals(3, $updatedEvent3[0]['room']['id']);
    }

    public function testDeleteSingleEvent()
    {
        $initialEvents = self::$eventsModel->getEvents();

        $this->assertCount(19, $initialEvents);

        self::$eventsModel->deleteSingleEvent(19);

        $updatedEvents = self::$eventsModel->getEvents();

        $this->assertCount(18, $updatedEvents);

        $deletedEvent = self::$eventsModel->getEvents(19);

        $this->assertCount(0, $deletedEvent);
    }

    public function testDeleteMultipleEvents()
    {
        $recurId = self::$eventsModel->getEvents(14)[0]['recur_id'];
        
        $initialEvents = self::$eventsModel->getEvents();

        $this->assertCount(18, $initialEvents);

        self::$eventsModel->deleteMultipleEvents(14, $recurId);

        $updatedEvents = self::$eventsModel->getEvents();

        $this->assertCount(15, $updatedEvents);

        $deletedEvent1 = self::$eventsModel->getEvents(14);
        $deletedEvent2 = self::$eventsModel->getEvents(15);
        $deletedEvent3 = self::$eventsModel->getEvents(16);

        $this->assertCount(0, $deletedEvent1);
        $this->assertCount(0, $deletedEvent2);
        $this->assertCount(0, $deletedEvent3);
    }

    public function testDeleteFutureEventsOfUser()
    {
        $initialUserEvents = self::$eventsModel->getEvents(null, [
            'user_id' => 2
        ]);

        $this->assertCount(8, $initialUserEvents);

        self::$eventsModel->deleteFutureEventsOfUser(2);

        $updatedUserEvents = self::$eventsModel->getEvents(null, [
            'user_id' => 2
        ]);

        $this->assertCount(5, $updatedUserEvents);
    }

}