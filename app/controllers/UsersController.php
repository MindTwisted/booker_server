<?php

namespace app\controllers;

use libs\View;
use libs\Auth;
use libs\Validator\Validator;
use libs\Input\Input;

use app\models\UsersModel;

class UsersController
{
    /**
     * UsersModel instance
     */
    protected $usersModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    /**
     * Get all users
     */
    public function index()
    {
        $users = $this->usersModel->getUsers();

        return View::render([
            'data' => $users
        ]);
    }

    /**
     * Get user with provided id
     */
    public function show($id)
    {
        $user = $this->usersModel->getUsers($id);

        if (count($user) === 0)
        {
            return View::render([
                'text' => "Not found."
            ], 404);
        }

        return View::render([
            'data' => $user
        ]);
    }

    /**
     * Create new user
     */
    public function store()
    {    
        $validator = Validator::make([
            'name' => "required|min_length:6",
            'email' => "required|email|unique:users:email",
            'password' => "required|min_length:6"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $name = Input::get('name');
        $email = Input::get('email');
        $password = Input::get('password');

        $id = $this->usersModel->addUser($name, $email, $password);

        return View::render([
            'text' => "User '$name' was successfully registered.",
            'data' => ['id' => $id]
        ]);
    }

    /**
     * Update user with provided id
     */
    public function update($id)
    {
        $validator = Validator::make([
            'name' => "required|min_length:6",
            'email' => "required|email|unique:users:email:$id",
            'password' => "min_length:6"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $user = $this->usersModel->getUsers($id);

        if (count($user) === 0)
        {
            return View::render([
                'text' => "Not found."
            ], 404);
        }

        $name = Input::get('name');
        $email = Input::get('email');
        $password = Input::get('password');

        $this->usersModel->updateUser($id, $name, $email, $password);

        return View::render([
            'text' => "User '$name' was successfully updated."
        ]);
    }

    /**
     * Delete user with provided id
     */
    public function delete($id)
    {
        if (+$id === +Auth::user()['id'])
        {
            return View::render([
                'text' => 'User can\'t delete himself.'
            ], 409);
        }

        $user = $this->usersModel->getUsers($id);

        if (count($user) === 0)
        {
            return View::render([
                'text' => "Not found."
            ], 404);
        }

        // TODO delete coming events

        $this->usersModel->deleteUser($id);

        return View::render([
            'text' => "User with id '$id' was successfully deleted."
        ]);
    }
}