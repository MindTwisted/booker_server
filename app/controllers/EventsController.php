<?php

namespace app\controllers;

use libs\View;
use libs\Auth;
use libs\Validator\Validator;
use libs\Input\Input;

use app\models\EventsModel;
use app\models\UsersModel;

class EventsController
{
    /**
     * EventsModel instance
     */
    protected $eventsModel;

     /**
     * UsersModel instance
     */
    protected $usersModel;

    /**
     * Check if timestamp is weekend
     */
    private function isWeekend($timestamp)
    {
        return date('N', $timestamp) >= 6;
    }

    /**
     * Check if timestamp is between 08:00:00 and 20:00:00
     */
    private function isWorkingHours($timestamp)
    {
        $startTime = \DateTime::createFromFormat('H:i:s', '08:00:00');
        $endTime = \DateTime::createFromFormat('H:i:s', '20:00:00');
        $checkTime = \DateTime::createFromFormat('H:i:s', date('H:i:s', $timestamp));

        return $checkTime >= $startTime && $checkTime <= $endTime;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->eventsModel = new EventsModel();
        $this->usersModel = new UsersModel();
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
            $filters['user_id'] = $user_id;
        }

        if (null !== $room_id)
        {
            $filters['room_id'] = $room_id;
        }

        if (null !== $start_time)
        {
            $filters['start_time'] = $start_time;
        }

        if (null !== $end_time)
        {
            $filters['end_time'] = $end_time;
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

    /**
     * Create new event
     */
    public function store()
    {   
        $validator = Validator::make([
            'description' => "required|min_length:5",
            'user_id' => "required|exists:users:id",
            'room_id' => "required|exists:rooms:id",
            'start_time' => "required|integer",
            'end_time' => "required|integer",
            'recur_type' => "included:(weekly, bi-weekly, monthly)",
            'recur_duration' => "required_with:recur_type|integer|min:1|max:4"
        ]);

        if ($validator->fails())
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => $validator->errors()
            ], 422);
        }

        $description = Input::get('description');
        $userId = Input::get('user_id');
        $roomId = Input::get('room_id');
        $startTime = Input::get('start_time');
        $endTime = Input::get('end_time');

        $user = $this->usersModel->getUsers($userId);
        $authUser = Auth::user();

        if (count($user) === 0)
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => ['user_id' => ["User_id field value doesn't exists in database."]]
            ], 422);
        }

        if (+$authUser['id'] !== +$userId
            && $authUser['role'] !== 'admin')
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => ['user_id' => ["User_id field doesn't equals id of authenticated user."]]
            ], 422);
        }

        if ($endTime < $startTime)
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => ['end_time' => ["End_time field timestamp can't be less than start_time."]]
            ], 422);
        }

        if ($endTime - $startTime < 1800)
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => ['end_time' => ["End_time field timestamp must be greater than start_time for min 30 minutes."]]
            ], 422);
        }

        if ($endTime - $startTime > 43200)
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => ['end_time' => ["End_time field timestamp must be greater than start_time for max 12 hours."]]
            ], 422);
        }

        if ($this->isWeekend($startTime))
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => ['start_time' => ["Start_time field timestamp can't be a weekend."]]
            ], 422);
        }

        if ($this->isWeekend($endTime))
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => ['end_time' => ["End_time field timestamp can't be a weekend."]]
            ], 422);
        }

        if (!$this->isWorkingHours($startTime))
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => ['start_time' => ["Start_time field timestamp must be between 08:00 and 20:00."]]
            ], 422);
        }

        if (!$this->isWorkingHours($endTime))
        {
            return View::render([
                'text' => 'The credentials you supplied were not correct.',
                'data' => ['end_time' => ["End_time field timestamp must be between 08:00 and 20:00."]]
            ], 422);
        }

        dd('stop');

        // TODO check room availability

        

        // $id = $this->roomsModel->addRoom($name);

        // return View::render([
        //     'text' => "Room '$name' was successfully added.",
        //     'data' => ['id' => $id]
        // ]);
    }
}