<?php

namespace app\controllers;

use libs\View;
use libs\Auth;
use libs\Validator\Validator;
use libs\Input\Input;

class AuthController
{
    /**
     * Get auth user data
     */
    public function index()
    {
        $user = Auth::user();
        
        return View::render([
            'data' => $user
        ]);
    }

    /**
     * Login user
     */
    public function store()
    {
        $validator = Validator::make([
            'email' => "required|email",
            'password' => "required"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $email = Input::get('email');
        $password = Input::get('password');

        $login = Auth::login($email, $password);

        return View::render([
            'text' => "User '{$login['name']}' was successfully logged in.",
            'data' => [
                'token' => $login['token'],
                'role' => $login['role']
            ]
        ]);        
    }
}