<?php

namespace app\controllers;

use libs\View;
use libs\Auth;
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
     * Get recurrent events timestamps
     */
    private function getRecurTimestamps($startTime, $endTime, $recurType, $recurDuration)
    {
        $results = [
            [
                'startTime' => +$startTime,
                'endTime' => +$endTime
            ]
        ];

        if ($recurType === 'weekly')
        {
            $localST = $startTime;
            $localET = $endTime;

            for ($i = 1; $i <= $recurDuration; $i++)
            {
                $localST = strtotime('+1 week', $localST);
                $localET = strtotime('+1 week', $localET);

                $results[] = [
                    'startTime' => $localST,
                    'endTime' => $localET
                ];
            }
        }

        if ($recurType === 'bi-weekly')
        {
            $localST = $startTime;
            $localET = $endTime;

            for ($i = 1; $i <= $recurDuration; $i++)
            {
                $localST = strtotime('+2 week', $localST);
                $localET = strtotime('+2 week', $localET);

                $results[] = [
                    'startTime' => $localST,
                    'endTime' => $localET
                ];
            }
        }

        if ($recurType === 'monthly')
        {
            $results[] = [
                'startTime' => strtotime('+4 week', $startTime),
                'endTime' => strtotime('+4 week', $endTime)
            ];
        }

        return $results;
    }

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
        $filters = Input::only(['user_id', 'room_id', 'start_time', 'end_time']);

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
            'user_id' => "required|exists_soft:users:id:is_active|auth_user:id:admin",
            'room_id' => "required|exists:rooms:id",
            'start_time' => "required|integer|ts_not_in_past|ts_not_weekend|ts_in_hours_range:(08:00:00, 20:00:00)",
            'end_time' => "required|integer|ts_bigger_than:start_time|ts_bigger_min:start_time:1800|ts_bigger_max:start_time:43200|ts_not_weekend|ts_in_hours_range:(08:00:00, 20:00:00)",
            'recur_type' => "included:(weekly, bi-weekly, monthly)",
            'recur_duration' => "required_with:recur_type|integer|min:1|max:4|recur_duration:recur_type"
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
        $recurType = Input::get('recur_type');
        $recurDuration = Input::get('recur_duration');

        $eventsTimestamps = $this->getRecurTimestamps($startTime, $endTime, $recurType, $recurDuration);
        $events = $this->eventsModel->getEventsByTimestamps($roomId, $eventsTimestamps);

        if (count($events) > 0)
        {
            return View::render([
                'text' => "Room with id '$roomId' is not available at the specified time."
            ], 422);
        }

        $this->eventsModel->addEvent($userId, $roomId, $description, $eventsTimestamps);
        
        return View::render([
            'text' => "Events in room with id '$roomId' was successfully added."
        ]);
    }

    /**
     * Update event with provided id
     */
    public function update($id)
    {
        $validator = Validator::make([
            'description' => "required|min_length:5",
            'user_id' => "required|exists_soft:users:id:is_active|auth_user:id:admin",
            'room_id' => "required|exists:rooms:id",
            'recur_id' => "exists_spec:events:id=$id:recur_id",
            'start_time' => "required|integer|ts_not_in_past|ts_not_weekend|ts_in_hours_range:(08:00:00, 20:00:00)",
            'end_time' => "required|integer|ts_bigger_than:start_time|ts_bigger_min:start_time:1800|ts_bigger_max:start_time:43200|ts_not_weekend|ts_in_hours_range:(08:00:00, 20:00:00)",
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
        $recurId = Input::get('recur_id');
        $startTime = Input::get('start_time');
        $endTime = Input::get('end_time');

        $event = $this->eventsModel->getEvents($id)[0];
        $authUser = Auth::user();

        if ('admin' !== $authUser['role'] 
            && +$authUser['id'] !== +$event['user']['id'])
        {
            return View::render([
                'text' => "Permission denied."
            ], 403);
        }
        
        if ($event['start_time'] < time())
        {
            return View::render([
                'text' => "Past events can't be edited."
            ], 403);
        }

        if (null === $recurId)
        {
            $eventsTimestamps = [['startTime' => $startTime, 'endTime' => $endTime]];
    
            $events = $this->eventsModel->getEventsByTimestamps($roomId, $eventsTimestamps);

            if (count($events) > 0)
            {
                return View::render([
                    'text' => "Room with id '$roomId' is not available at the specified time."
                ], 422);
            }

            $this->eventsModel->updateSingleEvent($userId, $roomId, $description, $eventsTimestamps);
        }

        return View::render([
            'text' => "Events in room with id '$roomId' was successfully updated."
        ]);
    }
}