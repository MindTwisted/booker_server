<?php

namespace app\controllers;

use libs\View;
use libs\Validator\Validator;
use libs\Input\Input;

use app\models\EventsModel;

class EventsController
{
    /**
     * EventsModel instance
     */
    protected $eventsModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->eventsModel = new EventsModel();
    }

    /**
     * Get all events
     */
    public function index()
    {
        $user_id = Input::get('user_id');
        $room_id = Input::get('room_id');
        $start_time = Input::get('start_time');
        $end_time = Input::get('end_time');

        $filters = [];
        
        if (null !== $user_id)
        {
            $filters[] = compact('user_id');
        }

        if (null !== $room_id)
        {
            $filters[] = compact('room_id');
        }

        if (null !== $start_time)
        {
            $filters[] = compact('start_time');
        }

        if (null !== $end_time)
        {
            $filters[] = compact('end_time');
        }

        $events = $this->eventsModel->getEvents(null, $filters);

        return View::render([
            'data' => $events
        ]);
    }

    /**
     * Get event with provided id
     */
    public function show($id)
    {
        $event = $this->eventsModel->getEvents($id);

        return View::render([
            'data' => $event
        ]);
    }
}