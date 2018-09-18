<?php

use libs\Router;

// Auth routes
Router::add('auth.index', [
    'url' => '/api/auth',
    'method' => 'GET',
    'controller' => ['app\controllers\AuthController', 'index'],
    'filters' => [
        'permission' => 'isAuth'
    ]
]);

Router::add('auth.store', [
    'url' => '/api/auth',
    'method' => 'POST',
    'controller' => ['app\controllers\AuthController', 'store']
]);

// Users routes
Router::add('users.index', [
    'url' => '/api/users',
    'method' => 'GET',
    'controller' => ['app\controllers\UsersController', 'index'],
    'filters' => [
        'permission' => 'isAdmin'
    ]
]);

Router::add('users.show', [
    'url' => '/api/users/:id',
    'method' => 'GET',
    'controller' => ['app\controllers\UsersController', 'show'],
    'filters' => [
        'permission' => 'isAdmin',
        'paramValidation' => 'exists:users:id'
    ]
]);

Router::add('users.store', [
    'url' => '/api/users',
    'method' => 'POST',
    'controller' => ['app\controllers\UsersController', 'store'],
    'filters' => [
        'permission' => 'isAdmin'
    ]
]);

Router::add('users.update', [
    'url' => '/api/users/:id',
    'method' => 'PUT',
    'controller' => ['app\controllers\UsersController', 'update'],
    'filters' => [
        'permission' => 'isAdmin',
        'paramValidation' => 'exists:users:id'
    ]
]);

Router::add('users.delete', [
    'url' => '/api/users/:id',
    'method' => 'DELETE',
    'controller' => ['app\controllers\UsersController', 'delete'],
    'filters' => [
        'permission' => 'isAdmin',
        'paramValidation' => 'exists:users:id'
    ]
]);

// Rooms routes
Router::add('rooms.index', [
    'url' => '/api/rooms',
    'method' => 'GET',
    'controller' => ['app\controllers\RoomsController', 'index'],
    'filters' => [
        'permission' => 'isAuth'
    ]
]);

Router::add('rooms.show', [
    'url' => '/api/rooms/:id',
    'method' => 'GET',
    'controller' => ['app\controllers\RoomsController', 'show'],
    'filters' => [
        'permission' => 'isAuth',
        'paramValidation' => 'exists:rooms:id'
    ]
]);


Router::add('rooms.store', [
    'url' => '/api/rooms',
    'method' => 'POST',
    'controller' => ['app\controllers\RoomsController', 'store'],
    'filters' => [
        'permission' => 'isAdmin'
    ]
]);

Router::add('rooms.update', [
    'url' => '/api/rooms/:id',
    'method' => 'PUT',
    'controller' => ['app\controllers\RoomsController', 'update'],
    'filters' => [
        'permission' => 'isAdmin',
        'paramValidation' => 'exists:rooms:id'
    ]
]);

Router::add('rooms.delete', [
    'url' => '/api/rooms/:id',
    'method' => 'DELETE',
    'controller' => ['app\controllers\RoomsController', 'delete'],
    'filters' => [
        'permission' => 'isAdmin',
        'paramValidation' => 'exists:rooms:id'
    ]
]);

// Events routes
Router::add('events.index', [
    'url' => '/api/events',
    'method' => 'GET',
    'controller' => ['app\controllers\EventsController', 'index'],
    'filters' => [
        'permission' => 'isAuth'
    ]
]);

Router::add('events.show', [
    'url' => '/api/events/:id',
    'method' => 'GET',
    'controller' => ['app\controllers\EventsController', 'show'],
    'filters' => [
        'permission' => 'isAuth',
        'paramValidation' => 'exists:events:id'
    ]
]);

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUrl = '/' . implode('/', array_slice(explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]), 3));

$matcher = Router::match($requestUrl, $requestMethod);

$controller = $matcher['settings']['controller'][0];
$method = $matcher['settings']['controller'][1];
$param = $matcher['param'];

(new $controller)->$method($param);