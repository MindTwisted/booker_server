<?php

namespace app\models;

class UsersModel extends Model
{
    /**
     * Get users from database
     */
    public function getUsers($id = null, $onlyActive = true)
    {
        $dbPrefix = self::$dbPrefix;

        $users = self::$builder->table("{$dbPrefix}users")
            ->fields(['id', 'name', 'email', 'role', 'is_active'])
            ->where(['1', '=', '1']);

        if ($onlyActive)
        {
            $users = $users->andWhere(['is_active', '=', '1']);
        }
        
        if (null !== $id)
        {
            $users = $users->andWhere(['id', '=', $id])->limit(1);
        }
            
        $users = $users->select()->run();

        return $users;
    }

    /**
     * Add user into database
     */
    public function addUser($name, $email, $password)
    {
        $dbPrefix = self::$dbPrefix;

        return self::$builder->table("{$dbPrefix}users")
            ->fields(['name', 'email', 'password'])
            ->values([$name, $email, password_hash($password, PASSWORD_BCRYPT)])
            ->insert()
            ->run();
    }

    /**
     * Update user in database
     */
    public function updateUser($id, $name, $email, $password = null)
    {
        $dbPrefix = self::$dbPrefix;

        $fields = ['name', 'email'];
        $values = [$name, $email];

        if (null !== $password)
        {
            $fields[] = 'password';
            $values[] = password_hash($password, PASSWORD_BCRYPT);
        }

        self::$builder->table("{$dbPrefix}users")
            ->fields($fields)
            ->values($values)
            ->where(['id', '=', $id])
            ->limit(1)
            ->update()
            ->run();
    }

    /**
     * Delete user from database
     */
    public function deleteUser($id)
    {
        $dbPrefix = self::$dbPrefix;

        self::$builder->table("{$dbPrefix}users")
            ->fields(['is_active'])
            ->values([0])
            ->where(['id', '=', $id])
            ->limit(1)
            ->update()
            ->run();
    }
}