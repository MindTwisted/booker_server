<?php

namespace app\models;

class UsersModel extends Model
{
    // public function getAllUsers()
    // {
    //     $dbPrefix = self::$dbPrefix;

    //     $users = self::$builder->table("{$dbPrefix}users")
    //         ->fields(['id', 'name', 'email', 'role', 'discount'])
    //         ->select()
    //         ->run();

    //     return $users;
    // }

    // public function getUserById($id)
    // {
    //     $dbPrefix = self::$dbPrefix;

    //     $user = self::$builder->table("{$dbPrefix}users")
    //         ->fields(['id', 'name', 'email', 'role', 'discount'])
    //         ->where(['id', '=', $id])
    //         ->limit(1)
    //         ->select()
    //         ->run();

    //     return $user;
    // }

    // public function addUser($name, $email, $password)
    // {
    //     $dbPrefix = self::$dbPrefix;

    //     return self::$builder->table("{$dbPrefix}users")
    //         ->fields(['name', 'email', 'password'])
    //         ->values([$name, $email, password_hash($password, PASSWORD_BCRYPT)])
    //         ->insert()
    //         ->run();
    // }

    // public function updateUser($id, $name, $email, $password = null, $discount = null)
    // {
    //     $dbPrefix = self::$dbPrefix;

    //     $fields = ['name', 'email'];
    //     $values = [$name, $email];

    //     if (null !== $password)
    //     {
    //         $fields[] = 'password';
    //         $values[] = password_hash($password, PASSWORD_BCRYPT);
    //     }

    //     if (null !== $discount)
    //     {
    //         $fields[] = 'discount';
    //         $values[] = $discount;
    //     }

    //     self::$builder->table("{$dbPrefix}users")
    //         ->fields($fields)
    //         ->values($values)
    //         ->where(['id', '=', $id])
    //         ->limit(1)
    //         ->update()
    //         ->run();
    // }
}