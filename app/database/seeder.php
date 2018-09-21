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
          ['Michael Smith', 'smith@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user'],
          ['William Johnson', 'william@example.com', password_hash('secret', PASSWORD_BCRYPT), 'user']
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
          ['Meeting', '2018-09-18 09:00:01', '2018-09-18 10:59:59', 2, 1, NULL],
          ['Meeting', '2018-09-27 10:30:01', '2018-09-27 11:29:59', 2, 1, NULL],
          ['Meeting', '2018-10-04 10:30:01', '2018-10-04 11:29:59', 2, 1, NULL],
          ['Meeting', '2018-10-11 10:30:01', '2018-10-11 11:29:59', 2, 1, NULL],
          ['Meeting', '2018-10-18 12:00:01', '2018-10-18 14:59:59', 2, 1, NULL],
          ['Meeting', '2018-09-27 10:30:01', '2018-09-27 11:29:59', 3, 1, NULL],
          ['Meeting', '2018-10-04 10:30:01', '2018-10-04 11:29:59', 3, 1, NULL],
          ['Meeting', '2018-10-11 10:30:01', '2018-10-11 11:29:59', 3, 1, NULL],
          ['Meeting', '2018-10-18 12:00:01', '2018-10-18 14:59:59', 3, 1, NULL],
          ['Meeting', '2018-10-18 09:00:01', '2018-10-18 10:59:59', 1, 3, NULL],
          ['Meeting', '2018-10-19 09:00:01', '2018-10-19 10:59:59', 1, 2, 123456],
          ['Meeting', '2018-10-26 09:00:01', '2018-10-26 10:59:59', 1, 2, 123456],
          ['Meeting', '2018-11-02 09:00:01', '2018-11-02 10:59:59', 1, 2, 123456]
        )
      ->insert()
      ->run();