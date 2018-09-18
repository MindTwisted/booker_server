<?php

namespace libs;

use libs\QueryBuilder\src\QueryBuilder;
use libs\View;
use libs\JWT\JWT;

class Auth
{
    /**
     * QueryBuilder instance
     */
    private static $builder = null;

    /**
     * QueryBuilder database prefix
     */
    private static $dbPrefix = '';

    /**
     * Token expiration time
     */
    private static $tokenExpiresTime = 3600;

    /**
     * Current auth user
     */
    private static $user = null;

    /**
     * User of current instance
     */
    private $instanceUser = null;

    /**
     * Admin role check
     */
    private function isAdmin()
    {
        return 'admin' === $this->instanceUser['role'];
    }

    /**
     * Constructor
     */
    public function __construct($user)
    {
        $this->instanceUser = $user;
    }

    /**
     * Set database prefix
     */
    public static function setDbPrefix($prefix)
    {
        self::$dbPrefix = $prefix;
    }

    /**
     * Set QueryBuilder instance
     */
    public static function setBuilder(QueryBuilder $builder)
    {
        self::$builder = $builder;
    }

    /**
     * Set token expiration time
     */
    public static function setTokenExpiresTime($time)
    {
        self::$tokenExpiresTime = $time;
    }

    /**
     * Handle admin check
     */
    public function checkAdmin()
    {
        if (!$this->isAdmin())
        {
            return View::render([
                'text' => "Permission denied."
            ], 403);
        }

        return true;
    }

    /**
     * Login user
     */
    public static function login($email, $password)
    {
        $user = self::$builder->table(self::$dbPrefix . 'users')
                    ->fields(['*'])
                    ->where(['email', '=', $email])
                    ->limit(1)
                    ->select()
                    ->run();
        $user = isset($user[0]) ? $user[0] : null;

        if (null === $user 
            || !password_verify($password, $user['password']))
        {
            return View::render([
                'text' => "The credentials you supplied were not correct."
            ], 401);   
        }
            
        $token = JWT::sign(
            ['userId' => $user['id']], 
            SECRET_KEY, 
            ['expiresIn' => AUTH_TOKEN_EXPIRES]
        );

        return [
            'name' => $user['name'],
            'role' => $user['role'],
            'token' => $token
        ];
    }

    /**
     * Get logged in user
     */
    public static function user()
    {
        return self::$user;
    }

    /**
     * Handle auth check
     */
    public static function check()
    {
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

        if (null === $authHeader 
            || !preg_match('/^bearer\s.+$/i', $authHeader))
        {
            return View::render([
                'text' => "You must be a authenticated user to process this request."
            ], 401);
        }

        $token = explode(' ', $authHeader)[1];
        $payload = JWT::verify($token, SECRET_KEY);

        $userId = $payload['userId'];
        $usersTable = self::$dbPrefix . 'users';

        $user = self::$builder->table($usersTable)
                    ->fields(['id', 'name', 'email', 'role'])
                    ->where(['id', '=', $userId])
                    ->select()
                    ->run();

        if (count($user) === 0)
        {
            return View::render([
                'text' => "You must be a authenticated user to process this request."
            ], 401);
        }

        self::$user = $user[0];

        return new self(self::$user);
    }
}