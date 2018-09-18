<?php

namespace app\controllers;

use libs\View;
use libs\Validator\Validator;
use libs\Input\Input;

use app\models\RoomsModel;

class RoomsController
{
    /**
     * RoomsModel instance
     */
    protected $roomsModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roomsModel = new RoomsModel();
    }

    /**
     * Get all rooms
     */
    public function index()
    {
        $rooms = $this->roomsModel->getRooms();

        return View::render([
            'data' => $rooms
        ]);
    }

    /**
     * Get room with provided id
     */
    public function show($id)
    {
        $room = $this->roomsModel->getRooms($id);

        return View::render([
            'data' => $room
        ]);
    }

    /**
     * Create new room
     */
    public function store()
    {   
        $validator = Validator::make([
            'name' => "required|unique:rooms:name"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $name = Input::get('name');

        $id = $this->roomsModel->addRoom($name);

        return View::render([
            'text' => "Room '$name' was successfully added.",
            'data' => ['id' => $id]
        ]);
    }

    /**
     * Update room with provided id
     */
    public function update($id)
    {
        $validator = Validator::make([
            'name' => "required|unique:rooms:name:$id"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $name = Input::get('name');

        $this->roomsModel->updateRoom($id, $name);

        return View::render([
            'text' => "Room '$name' was successfully updated."
        ]);
    }

    /**
     * Delete room with provided id
     */
    public function delete($id)
    {
        $this->roomsModel->deleteRoom($id);

        return View::render([
            'text' => "Room with id '$id' was successfully deleted."
        ]);
    }
}