<?php

namespace app\models;

class RoomsModel extends Model
{
    /**
     * Get rooms from database
     */
    public function getRooms($id = null, $onlyActive = true)
    {
        $dbPrefix = self::$dbPrefix;

        $rooms = self::$builder->table("{$dbPrefix}rooms")
            ->fields(['id', 'name', 'is_active'])
            ->where(['1', '=', '1']);

        if ($onlyActive)
        {
            $rooms = $rooms->andWhere(['is_active', '=', '1']);
        }

        if (null !== $id)
        {
            $rooms = $rooms->andWhere(['id', '=', $id])->limit(1);
        }

        $rooms = $rooms->select()->run();

        return $rooms;
    }

    /**
     * Add room into database
     */
    public function addRoom($name)
    {
        $dbPrefix = self::$dbPrefix;

        return self::$builder->table("{$dbPrefix}rooms")
                    ->fields(['name'])
                    ->values([$name])
                    ->insert()
                    ->run();
    }

    /**
     * Update room in database
     */
    public function updateRoom($id, $name)
    {
        $dbPrefix = self::$dbPrefix;

        self::$builder->table("{$dbPrefix}rooms")
            ->fields(['name'])
            ->values([$name])
            ->where(['id', '=', $id])
            ->limit(1)
            ->update()
            ->run();
    }

    /**
     * Delete room from database
     */
    public function deleteRoom($id)
    {
        $dbPrefix = self::$dbPrefix;

        self::$builder->table("{$dbPrefix}rooms")
            ->fields(['is_active'])
            ->values([0])
            ->where(['id', '=', $id])
            ->limit(1)
            ->update()
            ->run();
    }
}