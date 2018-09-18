<?php

require_once '../bootstrap/env.php';
require_once '../app/config/config.php';
require_once '../bootstrap/autoload.php';
require_once '../bootstrap/start.php';

// Set QueryBuilder instance
$builderMySQL = $queryBuilder;

// Get tables prefix from config
$prefix = DB_TABLE_PREFIX;

// Seed users table
$builderMySQL->table("{$prefix}users")
      ->fields(['name', 'email', 'password', 'role'])
      ->values(
          ['John Walker', 'john@example.com', password_hash('secret', PASSWORD_BCRYPT), 'admin'],
          ['Michael Smith', 'smith@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user']
        )
      ->insert()
      ->run();

// Seed rooms table
$builderMySQL->table("{$prefix}rooms")
      ->fields(['name'])
      ->values(
          ['Boardroom 1'],
          ['Boardroom 2'],
          ['Boardroom 3']
        )
      ->insert()
      ->run();

// Seed events table
$builderMySQL->table("{$prefix}events")
      ->fields(['description', 'start_time', 'end_time', 'user_id', 'room_id', 'recur_id'])
      ->values(
          ['Meeting', '2018-09-18 09:00:00', '2018-09-18 11:00:00', 2, 1, NULL],
          ['Meeting', '2018-10-18 12:00:00', '2018-10-18 15:00:00', 2, 2, NULL],
          ['Meeting', '2018-10-18 09:00:00', '2018-10-18 11:00:00', 1, 3, NULL],
          ['Meeting', '2018-10-19 09:00:00', '2018-10-19 11:00:00', 1, 2, 123456],
          ['Meeting', '2018-10-26 09:00:00', '2018-10-26 11:00:00', 1, 2, 123456],
          ['Meeting', '2018-11-02 09:00:00', '2018-11-02 11:00:00', 1, 2, 123456]
        )
      ->insert()
      ->run();