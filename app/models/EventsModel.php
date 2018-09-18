<?php

namespace app\models;

class EventsModel extends Model
{
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
                    "UNIX_TIMESTAMP({$eventsTable}.start_time) as start_time", 
                    "UNIX_TIMESTAMP({$eventsTable}.end_time) as end_time", 
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
}