<?php

namespace app\models;

class EventsModel extends Model
{
    /**
     * Get events within provided timestamps
     */
    public function getEventsByTimestamps($roomId, $timestamps)
    {
        $dbPrefix = self::$dbPrefix;

        $sqlQuery = "
            SELECT 
                id,
                room_id,
                user_id,
                description, 
                UNIX_TIMESTAMP(start_time) - 1 as start_time,
                UNIX_TIMESTAMP(end_time) + 1 as end_time
            FROM {$dbPrefix}events
            WHERE room_id = '$roomId' AND (
        ";

        foreach ($timestamps as $ts)
        {
            $startTime = date('Y-m-d H:i:s', $ts['startTime']);
            $endTime = date('Y-m-d H:i:s', $ts['endTime']);

            $sqlQuery .= "(start_time >= '$startTime' AND start_time <= '$endTime') OR (end_time >= '$startTime' AND end_time <= '$endTime') OR ";
        }

        $sqlQuery = trim($sqlQuery, 'OR ');
        $sqlQuery .= ")";
        
        $events = self::$builder->raw($sqlQuery);
        $events = $events->fetchAll(\PDO::FETCH_ASSOC);

        return $events;
    }

    /**
     * Get events frame with provided recur id started from id
     */
    public function getEventsByRecurId($id, $recurId)
    {
        $dbPrefix = self::$dbPrefix;

        $eventsTable = "{$dbPrefix}events";

        $events = self::$builder->table($eventsTable)
            ->fields(
                [
                    'id',
                    'recur_id', 
                    'description',
                    "UNIX_TIMESTAMP(start_time) - 1 as start_time",
                    "UNIX_TIMESTAMP(end_time) + 1 as end_time"
                ])
            ->where(['recur_id', '=', $recurId])
            ->andWhere(['id', '>=', $id])
            ->select()
            ->run();

        return $events;
    }

    /**
     * Get events from database
     */
    public function getEvents($id = null, $filters = [])
    {
        $dbPrefix = self::$dbPrefix;
        $separator = '---';

        $eventsTable = "{$dbPrefix}events";
        $roomsTable = "{$dbPrefix}rooms";
        $usersTable = "{$dbPrefix}users";

        $events = self::$builder->table($eventsTable)
            ->join($roomsTable, ["{$roomsTable}.id", "{$eventsTable}.room_id"])
            ->join($usersTable, ["{$usersTable}.id", "{$eventsTable}.user_id"])
            ->fields(
                [
                    "{$eventsTable}.id", 
                    "{$eventsTable}.recur_id",
                    "{$eventsTable}.description", 
                    "UNIX_TIMESTAMP({$eventsTable}.start_time) - 1 as start_time", 
                    "UNIX_TIMESTAMP({$eventsTable}.end_time) + 1 as end_time", 
                    "UNIX_TIMESTAMP({$eventsTable}.created_at) as created_at",
                    "GROUP_CONCAT(DISTINCT {$roomsTable}.id, '$separator', {$roomsTable}.name) AS room",
                    "GROUP_CONCAT(DISTINCT {$usersTable}.id, '$separator', {$usersTable}.name, '$separator', {$usersTable}.email) AS user"
                ]
            )
            ->where(['1', '=', '1']);

        if (count($filters) > 0)
        {
            $whereClause = [];

            foreach($filters as $key => $value)
            {
                $expValue = explode(':', $value);

                $field = $key;
                $sign = count($expValue) > 1 ? $expValue[0] : '=';
                $sign = str_replace(['gt', 'lt', '='], ['>', '<', '='], $sign);
                $sign = in_array($sign, ['>', '<', '=']) ? $sign : '=';
                $value = count($expValue) > 1 ? $expValue[1] : $expValue[0];

                $whereClause[] = [$field, $sign, $value];
            }

            $events = $events->andWhere(...$whereClause);
        }

        if (null !== $id)
        {
            $events = $events->andWhere(["{$eventsTable}.id", '=', $id])->limit(1);
        }

        $events = $events->groupBy(["{$eventsTable}.id"])
            ->select()
            ->run();

        $events = array_map(function($event) use ($separator) {
            $user = explode($separator, $event['user']);
            $user = [
                'id' => $user[0],
                'name' => $user[1],
                'email' => $user[2]
            ];

            $room = explode($separator, $event['room']);
            $room = [
                'id' => $room[0],
                'name' => $room[1]
            ];

            $event['user'] = $user;
            $event['room'] = $room;
            
            return $event;
        }, $events);

        return $events;
    }

    /**
     * Add event into database
     */
    public function addEvent($userId, $roomId, $description, $timestamps)
    {
        $dbPrefix = self::$dbPrefix;

        $fields = ['description', 'start_time', 'end_time', 'user_id', 'room_id'];
        $values = [];

        $isRecur = count($timestamps) > 1;
        $recurId = time();
        
        if ($isRecur)
        {
            $fields[] = 'recur_id';
        }

        foreach ($timestamps as $ts)
        {
            $startTime = date('Y-m-d H:i:s', $ts['startTime'] + 1);
            $endTime = date('Y-m-d H:i:s', $ts['endTime'] - 1);

            $currentValue = [$description, $startTime, $endTime, $userId, $roomId];

            if ($isRecur)
            {
                $currentValue[] = $recurId;
            }

            $values[] = $currentValue;
        }

        self::$builder->table("{$dbPrefix}events")
            ->fields($fields)
            ->values(...$values)
            ->insert()
            ->run();
    }

    /**
     * Update single event in database
     */
    public function updateSingleEvent($id, $userId, $roomId, $description, $timestamp)
    {
        $dbPrefix = self::$dbPrefix;

        $fields = ['description', 'start_time', 'end_time', 'user_id', 'room_id', 'recur_id'];

        $startTime = date('Y-m-d H:i:s', $timestamp['startTime'] + 1);
        $endTime = date('Y-m-d H:i:s', $timestamp['endTime'] - 1);

        $values = [$description, $startTime, $endTime, $userId, $roomId, null];

        self::$builder->table("{$dbPrefix}events")
            ->fields($fields)
            ->values($values)
            ->where(['id', '=', $id])
            ->update()
            ->run();
    }

    /**
     * Update multiple events in database
     */
    public function updateMultipleEvents($userId, $roomId, $description, $oldEvents, $updatedTimestamps)
    {
        $dbPrefix = self::$dbPrefix;
        $recurId = time();
        $fields = ['description', 'start_time', 'end_time', 'user_id', 'room_id', 'recur_id'];

        foreach ($oldEvents as $key => $value)
        {
            $startTime = date('Y-m-d H:i:s', $updatedTimestamps[$key]['startTime'] + 1);
            $endTime = date('Y-m-d H:i:s', $updatedTimestamps[$key]['endTime'] - 1);
            $values = [$description, $startTime, $endTime, $userId, $roomId, $recurId];

            self::$builder->table("{$dbPrefix}events")
                ->fields($fields)
                ->values($values)
                ->where(['id', '=', $value['id']])
                ->update()
                ->run();
        }

        return $recurId;
    }
}